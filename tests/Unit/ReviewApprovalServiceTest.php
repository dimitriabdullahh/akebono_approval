<?php

namespace Tests\Unit;

use App\Models\DocumentRequest;
use App\Models\RequestDocument;
use App\Models\Supplier;
use App\Models\User;
use App\Services\ReviewApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewApprovalServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $reviewService;
    protected $admin;
    protected $documentRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->reviewService = app(ReviewApprovalService::class);
        $this->admin = User::factory()->create(['role' => 'admin_qa']);
        
        $supplier = Supplier::factory()->create();
        $this->documentRequest = DocumentRequest::create([
            'supplier_id' => $supplier->id,
            'model' => 'TEST-001',
            'part_name' => 'Test Part',
            'part_number' => 'PN-001',
            'due_date' => now()->addDays(30),
            'created_by' => $this->admin->id,
        ]);
    }

    /**
     * Test approve document method
     */
    public function test_approve_document_changes_status_to_approved()
    {
        $requestDoc = $this->documentRequest->requestDocuments()->first();
        $requestDoc->update(['status' => 'SUBMITTED']);

        $this->reviewService->approveDocument($requestDoc, $this->admin->id);

        $this->assertEquals('APPROVED', $requestDoc->fresh()->status);
        $this->assertEquals($this->admin->id, $requestDoc->fresh()->reviewed_by);
        $this->assertNotNull($requestDoc->fresh()->reviewed_at);
    }

    /**
     * Test reject document method
     */
    public function test_reject_document_changes_status_to_revise()
    {
        $requestDoc = $this->documentRequest->requestDocuments()->first();
        $requestDoc->update(['status' => 'SUBMITTED']);

        $this->reviewService->rejectDocument($requestDoc, 'Fix this document', $this->admin->id);

        $this->assertEquals('REVISE', $requestDoc->fresh()->status);
        $this->assertEquals('Fix this document', $requestDoc->fresh()->revision_note);
        $this->assertNotNull($requestDoc->fresh()->revision_deadline_at);
    }

    /**
     * Test revision deadline calculation
     */
    public function test_revision_deadline_is_calculated_correctly()
    {
        $requestDoc = $this->documentRequest->requestDocuments()->first();
        $requestDoc->update(['status' => 'SUBMITTED']);

        $beforeTime = now();
        $this->reviewService->rejectDocument($requestDoc, 'Revise', $this->admin->id);
        $afterTime = now();

        $requestDoc = $requestDoc->fresh();
        $this->assertTrue($requestDoc->revision_deadline_at->isBetween($beforeTime->addDays(2), $afterTime->addDays(2)->addMinutes(1)));
    }

    /**
     * Test user can review document in their scope
     */
    public function test_user_can_review_document_in_their_scope()
    {
        $qaUser = User::factory()->create(['role' => 'admin_qa']);
        $requestDoc = $this->documentRequest->requestDocuments()->first();
        $requestDoc->documentType()->update(['scope' => ['QA']]);

        $canReview = $this->reviewService->userCanReviewDocument($qaUser->id, $requestDoc);
        $this->assertTrue($canReview);
    }

    /**
     * Test user cannot review document outside their scope
     */
    public function test_user_cannot_review_document_outside_their_scope()
    {
        $purchasingUser = User::factory()->create(['role' => 'admin_purchasing']);
        $requestDoc = $this->documentRequest->requestDocuments()->first();
        $requestDoc->documentType()->update(['scope' => ['QA']]);

        $canReview = $this->reviewService->userCanReviewDocument($purchasingUser->id, $requestDoc);
        $this->assertFalse($canReview);
    }

    /**
     * Test all mandatory documents approved check
     */
    public function test_all_mandatory_documents_approved_returns_true_when_all_approved()
    {
        // Mark all as APPROVED
        $this->documentRequest->requestDocuments()->update(['status' => 'APPROVED']);

        $result = $this->reviewService->allMandatoryDocumentsApproved($this->documentRequest);
        $this->assertTrue($result);
    }

    /**
     * Test all mandatory documents approved check fails when one is not
     */
    public function test_all_mandatory_documents_approved_returns_false_when_not_all_approved()
    {
        // Mark only first as APPROVED
        $this->documentRequest->requestDocuments()->first()->update(['status' => 'APPROVED']);

        $result = $this->reviewService->allMandatoryDocumentsApproved($this->documentRequest);
        $this->assertFalse($result);
    }

    /**
     * Test get documents for review by user role
     */
    public function test_get_documents_for_review_returns_documents_in_user_scope()
    {
        $qaUser = User::factory()->create(['role' => 'admin_qa']);
        $requestDoc = $this->documentRequest->requestDocuments()->first();
        $requestDoc->update(['status' => 'SUBMITTED']);
        $requestDoc->documentType()->update(['scope' => ['QA']]);

        $documents = $this->reviewService->getDocumentsForReview($qaUser->id);

        $this->assertGreaterThan(0, $documents->count());
        $this->assertTrue($documents->contains($requestDoc));
    }

    /**
     * Test get revision documents near deadline
     */
    public function test_get_revision_documents_nearing_deadline()
    {
        $requestDoc = $this->documentRequest->requestDocuments()->first();
        $requestDoc->update([
            'status' => 'REVISE',
            'revision_deadline_at' => now()->addHours(12),
        ]);

        $documents = $this->reviewService->getRevisionDocumentsNearingDeadline();

        $this->assertGreaterThan(0, $documents->count());
        $this->assertTrue($documents->contains($requestDoc));
    }
}
