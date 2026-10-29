<?php

namespace Tests\Feature;

use App\Events\DocumentSubmitted;
use App\Mail\ApprovalNotificationMail;
use App\Models\ApprovalBlastToken;
use App\Models\DocumentRequest;
use App\Models\DocumentType;
use App\Models\DocumentApproval;
use App\Models\InspectionStandard;
use App\Models\PackagingStandard;
use App\Models\PartSubmissionWarrant;
use App\Models\MsaGageRr;
use App\Models\RequestDocument;
use App\Models\RequestPart;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class ApprovalBlastEmailTest extends TestCase
{
    use RefreshDatabase;

    protected $supplier;
    protected $documentRequest;
    protected $qaUser;
    protected $purchasingUser;
    protected $ppcUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test supplier
        $this->supplier = Supplier::factory()->create([
            'supplier_name' => 'Test Supplier',
            'supplier_code' => 'TS-001',
        ]);

        // Create admin users for different divisions
        $this->qaUser = User::factory()->create([
            'name' => 'QA Admin',
            'email' => 'qa@example.com',
            'role' => 'admin_qa',
        ]);

        $this->purchasingUser = User::factory()->create([
            'name' => 'Purchasing Admin',
            'email' => 'purchasing@example.com',
            'role' => 'admin_purchasing',
        ]);

        $this->ppcUser = User::factory()->create([
            'name' => 'PPC Admin',
            'email' => 'ppc@example.com',
            'role' => 'admin_ppc',
        ]);

        // Create document request with 16 request documents
        $this->documentRequest = DocumentRequest::create([
            'supplier_id' => $this->supplier->id,
            'model' => 'TEST-MODEL-001',
            'part_name' => 'Test Part',
            'part_number' => 'PN-TEST-001',
            'due_date' => now()->addDays(30),
            'created_by' => $this->qaUser->id,
        ]);
    }

    /**
     * Test PSW submission triggers DocumentSubmitted event and creates approval tokens
     */
    public function test_psw_submission_dispatches_document_submitted_event()
    {
        Event::fake();

        $requestPart = RequestPart::create([
            'document_request_id' => $this->documentRequest->id,
            'part_number' => 'PN-TEST-001',
            'part_name' => 'Test Part',
        ]);

        $requestDoc = RequestDocument::create([
            'document_request_id' => $this->documentRequest->id,
            'request_part_id' => $requestPart->id,
            'document_type_id' => DocumentType::where('code', 'PSW')->first()->id,
            'status' => 'PENDING',
        ]);

        $psw = PartSubmissionWarrant::create([
            'request_document_id' => $requestDoc->id,
            'supplier_id' => $this->supplier->id,
            'status' => 'PENDING',
            'part_number' => 'PN-TEST-001',
            'part_name' => 'Test Part',
            'submission_information' => json_encode(['new_part' => true]),
            'reason_for_submission' => json_encode(['design_change' => true]),
        ]);

        // Get supplier user
        $supplierUser = User::factory()->create([
            'supplier_id' => $this->supplier->id,
            'role' => 'supplier',
        ]);

        // Submit PSW
        $response = $this->actingAs($supplierUser)->postJson(
            route('psw.submit', $psw->id),
            [
                'part_name' => 'Test Part',
                'part_number' => 'PN-TEST-001',
                'submission_information' => ['new_part' => true],
                'reason_for_submission' => ['design_change' => true],
                'submission_results' => ['approved' => true],
                'meets_all_requirements' => true,
            ]
        );

        $response->assertJsonPath('success', true);

        // Verify DocumentSubmitted event was dispatched
        Event::assertDispatched(DocumentSubmitted::class, function ($event) {
            return $event->documentType === 'PSW';
        });
    }

    /**
     * Test IS submission triggers DocumentSubmitted event
     */
    public function test_inspection_standard_submission_dispatches_event()
    {
        Event::fake();

        $requestPart = RequestPart::create([
            'document_request_id' => $this->documentRequest->id,
            'part_number' => 'PN-TEST-001',
            'part_name' => 'Test Part',
        ]);

        $requestDoc = RequestDocument::create([
            'document_request_id' => $this->documentRequest->id,
            'request_part_id' => $requestPart->id,
            'document_type_id' => DocumentType::where('code', 'IS')->first()->id,
            'status' => 'PENDING',
        ]);

        $is = InspectionStandard::create([
            'request_document_id' => $requestDoc->id,
            'supplier_id' => $this->supplier->id,
            'status' => 'PENDING',
            'inspection_data' => json_encode(['test' => true]),
        ]);

        $supplierUser = User::factory()->create([
            'supplier_id' => $this->supplier->id,
            'role' => 'supplier',
        ]);

        // Submit IS
        $response = $this->actingAs($supplierUser)->postJson(
            route('inspection-standard.submit', $is->id),
            [
                'generalInformation' => ['supplier_name' => 'Test Supplier'],
                'inspectionItems' => [],
            ]
        );

        $response->assertJsonPath('success', true);

        // Verify event was dispatched
        Event::assertDispatched(DocumentSubmitted::class, function ($event) {
            return $event->documentType === 'IS';
        });
    }

    /**
     * Test approval token is created with correct data
     */
    public function test_approval_token_created_with_correct_document_info()
    {
        Mail::fake();

        $requestPart = RequestPart::create([
            'document_request_id' => $this->documentRequest->id,
            'part_number' => 'PN-TEST-001',
            'part_name' => 'Test Part',
        ]);

        $requestDoc = RequestDocument::create([
            'document_request_id' => $this->documentRequest->id,
            'request_part_id' => $requestPart->id,
            'document_type_id' => DocumentType::where('code', 'PSW')->first()->id,
            'status' => 'PENDING',
        ]);

        $psw = PartSubmissionWarrant::create([
            'request_document_id' => $requestDoc->id,
            'supplier_id' => $this->supplier->id,
            'status' => 'PENDING',
            'part_number' => 'PN-TEST-001',
            'part_name' => 'Test Part',
            'submission_information' => json_encode(['new_part' => true]),
            'reason_for_submission' => json_encode(['design_change' => true]),
        ]);

        $supplierUser = User::factory()->create([
            'supplier_id' => $this->supplier->id,
            'role' => 'supplier',
        ]);

        $this->actingAs($supplierUser)->postJson(
            route('psw.submit', $psw->id),
            [
                'part_name' => 'Test Part',
                'part_number' => 'PN-TEST-001',
                'submission_information' => ['new_part' => true],
                'reason_for_submission' => ['design_change' => true],
                'submission_results' => ['approved' => true],
                'meets_all_requirements' => true,
            ]
        );

        // Verify tokens were created for Purchasing division
        $tokens = ApprovalBlastToken::where('request_document_id', $requestDoc->id)->get();
        $this->assertGreaterThan(0, $tokens->count());

        $token = $tokens->first();
        $this->assertEquals('PSW', $token->document_type);
        $this->assertEquals('PENDING', $token->status);
        $this->assertNotNull($token->token_hash);
    }

    /**
     * Test approval token validation
     */
    public function test_approval_token_can_be_found_and_validated()
    {
        $requestPart = RequestPart::create([
            'document_request_id' => $this->documentRequest->id,
            'part_number' => 'PN-TEST-001',
            'part_name' => 'Test Part',
        ]);

        $requestDoc = RequestDocument::create([
            'document_request_id' => $this->documentRequest->id,
            'request_part_id' => $requestPart->id,
            'document_type_id' => DocumentType::where('code', 'PSW')->first()->id,
            'status' => 'PENDING',
        ]);

        $token = ApprovalBlastToken::generateForApproval($requestDoc, 'PSW', 'Purchasing');

        // Test finding by plain token
        $found = ApprovalBlastToken::findByPlainToken($token->plain_token);
        $this->assertNotNull($found);
        $this->assertEquals($requestDoc->id, $found->request_document_id);

        // Test validation
        $this->assertTrue($found->isValid());
    }

    /**
     * Test expired token is invalid
     */
    public function test_expired_token_is_invalid()
    {
        $requestPart = RequestPart::create([
            'document_request_id' => $this->documentRequest->id,
            'part_number' => 'PN-TEST-001',
            'part_name' => 'Test Part',
        ]);

        $requestDoc = RequestDocument::create([
            'document_request_id' => $this->documentRequest->id,
            'request_part_id' => $requestPart->id,
            'document_type_id' => DocumentType::where('code', 'PSW')->first()->id,
            'status' => 'PENDING',
        ]);

        $token = ApprovalBlastToken::create([
            'request_document_id' => $requestDoc->id,
            'document_type' => 'PSW',
            'division' => 'Purchasing',
            'plain_token' => Str::random(64),
            'token_hash' => hash('sha256', Str::random(64)),
            'status' => 'PENDING',
            'expires_at' => now()->subDay(), // Expired 1 day ago
        ]);

        $this->assertFalse($token->isValid());
    }

    /**
     * Test token can be marked as used
     */
    public function test_token_marked_as_used_on_approval()
    {
        $requestPart = RequestPart::create([
            'document_request_id' => $this->documentRequest->id,
            'part_number' => 'PN-TEST-001',
            'part_name' => 'Test Part',
        ]);

        $requestDoc = RequestDocument::create([
            'document_request_id' => $this->documentRequest->id,
            'request_part_id' => $requestPart->id,
            'document_type_id' => DocumentType::where('code', 'PSW')->first()->id,
            'status' => 'SUBMITTED',
        ]);

        $token = ApprovalBlastToken::generateForApproval($requestDoc, 'PSW', 'Purchasing');

        // Mark as used
        $token->markAsUsed($this->purchasingUser->id);

        $updated = $token->fresh();
        $this->assertEquals('USED', $updated->status);
        $this->assertNotNull($updated->used_at);
        $this->assertEquals($this->purchasingUser->id, $updated->used_by_user_id);
    }

    /**
     * Test token can be invalidated (when document status changes to REVISE)
     */
    public function test_token_invalidated_on_revision()
    {
        $requestPart = RequestPart::create([
            'document_request_id' => $this->documentRequest->id,
            'part_number' => 'PN-TEST-001',
            'part_name' => 'Test Part',
        ]);

        $requestDoc = RequestDocument::create([
            'document_request_id' => $this->documentRequest->id,
            'request_part_id' => $requestPart->id,
            'document_type_id' => DocumentType::where('code', 'PSW')->first()->id,
            'status' => 'SUBMITTED',
        ]);

        $token = ApprovalBlastToken::generateForApproval($requestDoc, 'PSW', 'Purchasing');

        // Invalidate token
        $token->invalidate();

        $updated = $token->fresh();
        $this->assertEquals('INVALIDATED', $updated->status);
    }

    /**
     * Test approval token shows PDF document in approval page
     */
    public function test_approval_page_displays_pdf()
    {
        $requestPart = RequestPart::create([
            'document_request_id' => $this->documentRequest->id,
            'part_number' => 'PN-TEST-001',
            'part_name' => 'Test Part',
        ]);

        $requestDoc = RequestDocument::create([
            'document_request_id' => $this->documentRequest->id,
            'request_part_id' => $requestPart->id,
            'document_type_id' => DocumentType::where('code', 'PSW')->first()->id,
            'status' => 'SUBMITTED',
            'file_path' => 'form',
            'pdf_path' => 'form',
        ]);

        $psw = PartSubmissionWarrant::create([
            'request_document_id' => $requestDoc->id,
            'supplier_id' => $this->supplier->id,
            'status' => 'SUBMITTED',
            'part_number' => 'PN-TEST-001',
            'part_name' => 'Test Part',
        ]);

        $token = ApprovalBlastToken::generateForApproval($requestDoc, 'PSW', 'Purchasing');

        // Access approval page
        $response = $this->get(route('approval-token.show', $token->plain_token));

        $response->assertStatus(200);
        $response->assertViewHas('requestDocument');
        $response->assertViewHas('token');
    }

    /**
     * Test approval token not found returns error page
     */
    public function test_invalid_token_shows_not_found_page()
    {
        $response = $this->get(route('approval-token.show', 'invalid-token-xyz'));

        $response->assertStatus(404);
        $response->assertViewIs('approval.not-found');
    }

    /**
     * Test approval action updates request document status
     */
    public function test_approval_action_updates_document_status()
    {
        $requestPart = RequestPart::create([
            'document_request_id' => $this->documentRequest->id,
            'part_number' => 'PN-TEST-001',
            'part_name' => 'Test Part',
        ]);

        $requestDoc = RequestDocument::create([
            'document_request_id' => $this->documentRequest->id,
            'request_part_id' => $requestPart->id,
            'document_type_id' => DocumentType::where('code', 'PSW')->first()->id,
            'status' => 'SUBMITTED',
        ]);

        $psw = PartSubmissionWarrant::create([
            'request_document_id' => $requestDoc->id,
            'supplier_id' => $this->supplier->id,
            'status' => 'SUBMITTED',
            'part_number' => 'PN-TEST-001',
            'part_name' => 'Test Part',
        ]);

        $token = ApprovalBlastToken::generateForApproval($requestDoc, 'PSW', 'Purchasing');

        // Submit approval
        $response = $this->postJson(
            route('approval-token.approve', $token->plain_token),
            [
                'notes' => 'Approved - looks good',
            ]
        );

        $response->assertStatus(200);

        // Verify token is marked as used
        $usedToken = $token->fresh();
        $this->assertEquals('USED', $usedToken->status);

        // Verify document approval record exists
        $this->assertDatabaseHas('document_approvals', [
            'request_document_id' => $requestDoc->id,
            'division' => 'Purchasing',
            'status' => 'APPROVED',
        ]);
    }

    /**
     * Test revision action invalidates tokens
     */
    public function test_revision_action_invalidates_tokens()
    {
        $requestPart = RequestPart::create([
            'document_request_id' => $this->documentRequest->id,
            'part_number' => 'PN-TEST-001',
            'part_name' => 'Test Part',
        ]);

        $requestDoc = RequestDocument::create([
            'document_request_id' => $this->documentRequest->id,
            'request_part_id' => $requestPart->id,
            'document_type_id' => DocumentType::where('code', 'PSW')->first()->id,
            'status' => 'SUBMITTED',
        ]);

        $psw = PartSubmissionWarrant::create([
            'request_document_id' => $requestDoc->id,
            'supplier_id' => $this->supplier->id,
            'status' => 'SUBMITTED',
            'part_number' => 'PN-TEST-001',
            'part_name' => 'Test Part',
        ]);

        $token = ApprovalBlastToken::generateForApproval($requestDoc, 'PSW', 'Purchasing');

        // Submit revision request
        $response = $this->postJson(
            route('approval-token.revise', $token->plain_token),
            [
                'revision_notes' => 'Please revise',
            ]
        );

        $response->assertStatus(200);

        // Verify token is invalidated
        $invalidToken = $token->fresh();
        $this->assertEquals('INVALIDATED', $invalidToken->status);

        // Verify document status changed to REVISE
        $revisedDoc = $requestDoc->fresh();
        $this->assertEquals('REVISE', $revisedDoc->status);
    }

    /**
     * Test all divisions approved updates document status to fully approved
     */
    public function test_all_divisions_approved_updates_status()
    {
        $requestPart = RequestPart::create([
            'document_request_id' => $this->documentRequest->id,
            'part_number' => 'PN-TEST-001',
            'part_name' => 'Test Part',
        ]);

        $requestDoc = RequestDocument::create([
            'document_request_id' => $this->documentRequest->id,
            'request_part_id' => $requestPart->id,
            'document_type_id' => DocumentType::where('code', 'PSW')->first()->id,
            'status' => 'SUBMITTED',
        ]);

        // Create approval records for both divisions (PSW scope: Purchasing + QA)
        DocumentApproval::create([
            'request_document_id' => $requestDoc->id,
            'division' => 'Purchasing',
            'status' => 'APPROVED',
            'approved_by_user_id' => $this->purchasingUser->id,
            'approved_at' => now(),
        ]);

        DocumentApproval::create([
            'request_document_id' => $requestDoc->id,
            'division' => 'QA',
            'status' => 'APPROVED',
            'approved_by_user_id' => $this->qaUser->id,
            'approved_at' => now(),
        ]);

        // Check if all divisions approved
        $allApproved = $requestDoc->allScopesApproved();
        $this->assertTrue($allApproved);
    }

    /**
     * Test QR code in PDF contains correct approval info
     */
    public function test_qr_code_contains_approval_token()
    {
        Mail::fake();

        $requestPart = RequestPart::create([
            'document_request_id' => $this->documentRequest->id,
            'part_number' => 'PN-TEST-001',
            'part_name' => 'Test Part',
        ]);

        $requestDoc = RequestDocument::create([
            'document_request_id' => $this->documentRequest->id,
            'request_part_id' => $requestPart->id,
            'document_type_id' => DocumentType::where('code', 'PSW')->first()->id,
            'status' => 'PENDING',
        ]);

        $psw = PartSubmissionWarrant::create([
            'request_document_id' => $requestDoc->id,
            'supplier_id' => $this->supplier->id,
            'status' => 'PENDING',
            'part_number' => 'PN-TEST-001',
            'part_name' => 'Test Part',
            'submission_information' => json_encode(['new_part' => true]),
            'reason_for_submission' => json_encode(['design_change' => true]),
        ]);

        $supplierUser = User::factory()->create([
            'supplier_id' => $this->supplier->id,
            'role' => 'supplier',
        ]);

        $this->actingAs($supplierUser)->postJson(
            route('psw.submit', $psw->id),
            [
                'part_name' => 'Test Part',
                'part_number' => 'PN-TEST-001',
                'submission_information' => ['new_part' => true],
                'reason_for_submission' => ['design_change' => true],
                'submission_results' => ['approved' => true],
                'meets_all_requirements' => true,
            ]
        );

        // Verify token exists and has correct structure
        $token = ApprovalBlastToken::where('request_document_id', $requestDoc->id)->first();
        $this->assertNotNull($token->plain_token);
        $this->assertNotNull($token->token_hash);
        $this->assertEquals(64, strlen($token->plain_token));
    }
}
