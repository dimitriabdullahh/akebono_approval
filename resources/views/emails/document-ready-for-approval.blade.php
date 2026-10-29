@component('mail::message')
# Document Ready for Your Approval

Dear **{{ $approver->name }}** ({{ $approver->npk }}),

A document has been checked and is now ready for your approval.

## Document Details

- **Document Type:** {{ $documentType }}
- **Supplier:** {{ $supplier }}
- **Model:** {{ $model }}
- **Status:** Checked - Ready for Approval

@if($requestDocument->checked_at)
**Checked by:** {{ $requestDocument->checkedByEmployee->name }} ({{ $requestDocument->checkedByEmployee->npk }})  
**Checked on:** {{ $requestDocument->checked_at->format('d M Y H:i') }} WIB
@endif

@if($requestDocument->checker_notes)
**Checker Notes:**  
{{ $requestDocument->checker_notes }}
@endif

## Action Required

Please review and approve or request revision for this document.

@component('mail::button', ['url' => $reviewUrl, 'color' => 'success'])
Review & Approve Document
@endcomponent

---

**Important:** This document requires your approval as {{ $approver->division }} {{ $approver->role }}.

If you have any questions, please contact the Quality Assurance department.

Best regards,  
**Akebono Approval System**

@component('mail::subcopy')
This is an automated notification. If you're having trouble clicking the button, copy and paste the URL below into your web browser:  
{{ $reviewUrl }}
@endcomponent
@endcomponent
