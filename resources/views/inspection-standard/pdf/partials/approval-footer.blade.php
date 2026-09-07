{{-- Partial: blok approval Manufacturer / Supplier / Distribute To / AAIJ-QA --}}
<table class="approval-table spacer-xs" style="margin-top: 2px;">
    <tr>
        <th colspan="2" style="width: 18%; text-align: center; font-size: 10px;">MANUFACTURER</th>
        <th colspan="2" style="width: 18%; text-align: center; font-size: 10px;">SUPPLIER</th>
        <th colspan="3" style="width: 24%; text-align: center; font-size: 10px;">DISTRIBUTE TO : DIVISION</th>
        <th colspan="3" style="width: 40%; text-align: center; font-size: 10px;">{{ $qaLabel }}</th>
    </tr>
    <tr>
        <th style="width: 9%; font-size: 9px;">Approved</th>
        <th style="width: 9%; font-size: 9px;">Issued</th>
        <th style="width: 9%; font-size: 9px;">Approved</th>
        <th style="width: 9%; font-size: 9px;">Issued</th>
        <td class="bold" style="text-align: left; width: 10%; font-size: 10px; padding: 2px 3px;">Customer</td>
        <td class="bold" style="width: 2%; text-align: center; font-size: 10px; padding: 2px 3px;">:</td>
        <td style="text-align: left; width: 12%; font-size: 10px; padding: 2px 3px;">{{ $supplierInfo['customer'] ?? 'AAIJ (PUR & QA DIV)' }}</td>
        <th style="width: 13.33%; font-size: 9px;">Approved</th>
        <th style="width: 13.33%; font-size: 9px;">Checked</th>
        <th style="width: 13.34%; font-size: 9px;">Received</th>
    </tr>
    <tr>
        <td style="height: 18px;"></td><td></td><td></td><td></td>
        <td class="bold" style="text-align: left; font-size: 10px; padding: 2px 3px;">Supplier</td>
        <td class="bold" style="text-align: center; font-size: 10px; padding: 2px 3px;">:</td>
        <td style="text-align: left; font-size: 10px; padding: 2px 3px;">{{ $supplierInfo['supplier'] ?? '' }}</td>
        <td></td><td></td><td></td>
    </tr>
    <tr>
        <td colspan="4" style="height: 18px;"></td>
        <td class="bold" style="text-align: left; font-size: 10px; padding: 2px 3px;">Manufacturer</td>
        <td class="bold" style="text-align: center; font-size: 10px; padding: 2px 3px;">:</td>
        <td style="text-align: left; font-size: 10px; padding: 2px 3px;">{{ $supplierInfo['manufacturer'] ?? '' }}</td>
        <td colspan="3"></td>
    </tr>
</table>
