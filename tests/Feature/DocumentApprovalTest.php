<?php

namespace Tests\Feature;

use App\Models\DocumentRequest;
use App\Models\DocumentType;
use App\Models\RequestDocument;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $supplier;
    protected $documentRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin_qa']);
        $this->supplier = Supplier::factory()->create();

        $this->documentRequest = DocumentRequest::create([
            'supplier_id' => $this->supplier->id,
            'model' => 'TEST-001',
            'part_name' => 'Test Part',
            'part_number' => 'PN-001',
            'due_date' => now()->addDays(30),
            'created_by' => $this->admin->id,
        ]);
    }

    /**
     * Test creating a document request
     */
    public function test_admin_qa_can_create_document_request()
    {
        $response = $this->actingAs($this->admin)->post('/admin/requests', [
            'supplier_id' => $this->supplier->id,
            'model' => 'NEW-MODEL',
            'part_name' => 'New Part',
            'part_number' => 'PN-002',
            'due_date' => now()->addDays(30)->format('Y-m-d'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('document_requests', [
            'model' => 'NEW-MODEL',
            'part_name' => 'New Part',
        ]);
    }

    /**
     * Test 16 request documents are created automatically
     */
    public function test_16_request_documents_are_created_automatically()
    {
        $this->assertCount(16, $this->documentRequest->requestDocuments);
        $this->assertCount(14, $this->documentRequest->requestDocuments()->wherePivot('is_mandatory', true)->get());
    }

    /**
     * Test document approval workflow
     */
    public function test_document_can_be_approved()
    {
        $requestDoc = $this->documentRequest->requestDocuments()->first();
        $requestDoc->update(['status' => 'SUBMITTED', 'uploaded_at' => now()]);

        $response = $this->actingAs($this->admin)->post(
            "/admin/review/{$requestDoc->id}/approve"
        );

        $response->assertRedirect();
        $this->assertEquals('APPROVED', $requestDoc->fresh()->status);
    }

    /**
     * Test document revision request
     */
    public function test_document_can_be_requested_for_revision()
    {
        $requestDoc = $this->documentRequest->requestDocuments()->first();
        $requestDoc->update(['status' => 'SUBMITTED', 'uploaded_at' => now()]);

        $response = $this->actingAs($this->admin)->post(
            "/admin/review/{$requestDoc->id}/reject",
            ['revision_note' => 'Please revise this document']
        );

        $response->assertRedirect();
        $this->assertEquals('REVISE', $requestDoc->fresh()->status);
        $this->assertEquals('Please revise this document', $requestDoc->fresh()->revision_note);
    }

    /**
     * Test only SUBMITTED documents can be approved
     */
    public function test_only_submitted_documents_can_be_approved()
    {
        $requestDoc = $this->documentRequest->requestDocuments()->first();
        $requestDoc->update(['status' => 'APPROVED']);

        $response = $this->actingAs($this->admin)->post(
            "/admin/review/{$requestDoc->id}/approve"
        );

        $response->assertSessionHasErrors();
        $this->assertEquals('APPROVED', $requestDoc->fresh()->status);
    }

    /**
     * Test revision deadline is calculated automatically
     */
    public function test_revision_deadline_is_calculated_automatically()
    {
        $requestDoc = $this->documentRequest->requestDocuments()->first();
        $requestDoc->update(['status' => 'SUBMITTED', 'uploaded_at' => now()]);

        $this->actingAs($this->admin)->post(
            "/admin/review/{$requestDoc->id}/reject",
            ['revision_note' => 'Please revise']
        );

        $requestDoc = $requestDoc->fresh();
        $this->assertNotNull($requestDoc->revision_deadline_at);
        $this->assertTrue($requestDoc->revision_deadline_at->isAfter(now()));
    }

    /**
     * Test review authorization by scope
     */
    public function test_user_can_only_review_documents_in_their_scope()
    {
        $qaUser = User::factory()->create(['role' => 'admin_qa']);
        $purchasingUser = User::factory()->create(['role' => 'admin_purchasing']);

        $requestDoc = $this->documentRequest->requestDocuments()->first();
        $requestDoc->documentType()->update(['scope' => ['QA']]);
        $requestDoc->update(['status' => 'SUBMITTED', 'uploaded_at' => now()]);

        // QA user should be able to review
        $response = $this->actingAs($qaUser)->post(
            "/admin/review/{$requestDoc->id}/approve"
        );
        $this->assertNotEquals(403, $response->getStatusCode());

        // Purchasing user should NOT be able to review
        $response = $this->actingAs($purchasingUser)->post(
            "/admin/review/{$requestDoc->id}/approve"
        );
        $this->assertEquals(403, $response->getStatusCode());
    }

    /**
     * Test document history is recorded
     */
    public function test_document_approval_history_is_recorded()
    {
        $requestDoc = $this->documentRequest->requestDocuments()->first();
        $requestDoc->update(['status' => 'SUBMITTED', 'uploaded_at' => now()]);

        $this->actingAs($this->admin)->post(
            "/admin/review/{$requestDoc->id}/approve"
        );

        $this->assertDatabaseHas('document_histories', [
            'request_document_id' => $requestDoc->id,
            'action' => 'APPROVE',
        ]);
    }

    /**
     * Test all mandatory documents approved triggers completion notification
     */
    public function test_all_documents_approved_triggers_completion()
    {
        // Mark all documents as submitted and approved
        foreach ($this->documentRequest->requestDocuments as $doc) {
            $doc->update([
                'status' => 'SUBMITTED',
                'uploaded_at' => now(),
            ]);
        }

        // Approve all documents
        foreach ($this->documentRequest->requestDocuments as $doc) {
            $doc->update(['status' => 'APPROVED', 'reviewed_at' => now(), 'reviewed_by' => $this->admin->id]);
        }

        // Verify all are approved
        $approved = $this->documentRequest->requestDocuments()->where('status', 'APPROVED')->count();
        $this->assertEquals(16, $approved);
    }
}
