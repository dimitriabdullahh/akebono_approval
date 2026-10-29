{{-- Packaging Standard Preview Embed for Review Page --}}
<div style="padding: 20px; background: #fff; font-family: Arial, sans-serif; font-size: 12px;">
    {{-- Header --}}
    <div style="border: 2px solid #000; padding: 15px; margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <div style="flex: 1;">
                @if($ps->supplier)
                    <h3 style="margin: 0; font-weight: bold;">{{ strtoupper($ps->supplier->name) }}</h3>
                @endif
                <div style="margin-top: 8px; font-size: 11px;">
                    <div><strong>Document No:</strong> {{ $ps->document_no ?? '-' }}</div>
                    <div><strong>Revision:</strong> {{ $ps->revision ?? '-' }}</div>
                    <div><strong>Date:</strong> {{ $ps->date ? $ps->date->format('Y-m-d') : '-' }}</div>
                    <div><strong>Customer:</strong> {{ $ps->customer ?? 'PT. AKEBONO BRAKE ASTRA INDONESIA' }}</div>
                </div>
            </div>
            <div style="text-align: center; flex: 2;">
                <h1 style="margin: 0; font-size: 28px; font-weight: bold;">PACKAGING STANDARD</h1>
            </div>
            <div style="text-align: center; font-size: 10px; flex: 1;">
                <div style="border: 1px solid #000; padding: 5px; margin-bottom: 5px;">
                    <div><strong>Approved</strong></div>
                    <div style="min-height: 30px; margin: 5px 0;">{{ $ps->approved_by_name ?? '' }}</div>
                    <div>{{ $ps->approved_by_date ? $ps->approved_by_date->format('d/m/y') : '' }}</div>
                </div>
                <div style="border: 1px solid #000; padding: 5px; margin-bottom: 5px;">
                    <div><strong>Checked</strong></div>
                    <div style="min-height: 30px; margin: 5px 0;">{{ $ps->checked_by_name ?? '' }}</div>
                    <div>{{ $ps->checked_by_date ? $ps->checked_by_date->format('d/m/y') : '' }}</div>
                </div>
                <div style="border: 1px solid #000; padding: 5px;">
                    <div><strong>Prepared</strong></div>
                    <div style="min-height: 30px; margin: 5px 0;">{{ $ps->prepared_by_name ?? '' }}</div>
                    <div>{{ $ps->prepared_by_date ? $ps->prepared_by_date->format('d/m/y') : '' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- General Information --}}
    <div style="border: 1px solid #000; margin-bottom: 15px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px; border: 1px solid #000; width: 25%; font-weight: bold;">Part No.</td>
                <td style="padding: 8px; border: 1px solid #000; width: 75%;">{{ $ps->part_no ?? '-' }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #000; font-weight: bold;">Parts Name</td>
                <td style="padding: 8px; border: 1px solid #000;">{{ $ps->part_name ?? '-' }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #000; font-weight: bold;">Packaging Type</td>
                <td style="padding: 8px; border: 1px solid #000;">{{ $ps->packaging_type ?? '-' }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #000; font-weight: bold;">Packaging Size (mm)</td>
                <td style="padding: 8px; border: 1px solid #000;">{{ $ps->packaging_size_mm ?? '-' }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #000; font-weight: bold;">Packaging Lot (pcs/box)</td>
                <td style="padding: 8px; border: 1px solid #000;">{{ $ps->packaging_lot_pcs_box ?? '-' }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #000; font-weight: bold;">Net Weight (g/pc)</td>
                <td style="padding: 8px; border: 1px solid #000;">{{ $ps->net_weight_g_pc ?? '-' }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #000; font-weight: bold;">Total Weight (kg/box)</td>
                <td style="padding: 8px; border: 1px solid #000;">{{ $ps->total_weight_kg_box ?? '-' }}</td>
            </tr>
        </table>
    </div>

    {{-- Split View: Images and Tables --}}
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
        {{-- Left Column: Tables --}}
        <div>
            {{-- Part/Packaging Materials Table --}}
            <div style="border: 1px solid #000; margin-bottom: 15px;">
                <div style="background: #f0f0f0; padding: 8px; text-align: center; font-weight: bold; border-bottom: 1px solid #000;">
                    PART / PACKAGING MATERIALS
                </div>
                <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
                    <thead>
                        <tr style="background: #f5f5f5;">
                            <th style="padding: 5px; border: 1px solid #000; width: 8%;">No.</th>
                            <th style="padding: 5px; border: 1px solid #000; width: 25%;">Part No.</th>
                            <th style="padding: 5px; border: 1px solid #000; width: 35%;">Part Name</th>
                            <th style="padding: 5px; border: 1px solid #000; width: 20%;">Supplier</th>
                            <th style="padding: 5px; border: 1px solid #000; width: 12%;">Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $materials = $ps->form_data_json['materials'] ?? [];
                            if (!is_array($materials)) {
                                $materials = json_decode($materials, true) ?? [];
                            }
                        @endphp
                        @for($i = 0; $i < 6; $i++)
                            @php
                                $row = $materials[$i] ?? [];
                            @endphp
                            <tr>
                                <td style="padding: 5px; border: 1px solid #000; text-align: center;">{{ $i + 1 }}</td>
                                <td style="padding: 5px; border: 1px solid #000;">{{ $row['part_no'] ?? $row['partNo'] ?? '' }}</td>
                                <td style="padding: 5px; border: 1px solid #000;">{{ $row['part_name'] ?? $row['partName'] ?? '' }}</td>
                                <td style="padding: 5px; border: 1px solid #000;">{{ $row['supplier'] ?? '' }}</td>
                                <td style="padding: 5px; border: 1px solid #000; text-align: center;">{{ $row['qty'] ?? '' }}</td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>

            {{-- Revision Status Table --}}
            <div style="border: 1px solid #000;">
                <div style="background: #f0f0f0; padding: 8px; text-align: center; font-weight: bold; border-bottom: 1px solid #000;">
                    REVISION STATUS
                </div>
                <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
                    <thead>
                        <tr style="background: #f5f5f5;">
                            <th style="padding: 5px; border: 1px solid #000; width: 8%;">No.</th>
                            <th style="padding: 5px; border: 1px solid #000; width: 40%;">Modify Item</th>
                            <th style="padding: 5px; border: 1px solid #000; width: 17%;">Date</th>
                            <th style="padding: 5px; border: 1px solid #000; width: 20%;">Section</th>
                            <th style="padding: 5px; border: 1px solid #000; width: 15%;">Person</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $revisions = $ps->revision_history ?? $ps->form_data_json['revisionHistory'] ?? [];
                            if (!is_array($revisions)) {
                                $revisions = json_decode($revisions, true) ?? [];
                            }
                        @endphp
                        @for($i = 0; $i < 5; $i++)
                            @php
                                $row = $revisions[$i] ?? [];
                            @endphp
                            <tr>
                                <td style="padding: 5px; border: 1px solid #000; text-align: center;">{{ $i + 1 }}</td>
                                <td style="padding: 5px; border: 1px solid #000;">{{ $row['change'] ?? $row['item'] ?? $row['modify_item'] ?? '' }}</td>
                                <td style="padding: 5px; border: 1px solid #000; text-align: center;">{{ $row['date'] ?? '' }}</td>
                                <td style="padding: 5px; border: 1px solid #000; text-align: center;">{{ $row['section'] ?? '' }}</td>
                                <td style="padding: 5px; border: 1px solid #000; text-align: center;">{{ $row['person'] ?? '' }}</td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Right Column: Images --}}
        <div>
            {{-- Photographic Image of Packaging --}}
            <div style="border: 1px solid #000; padding: 10px; margin-bottom: 15px; min-height: 250px;">
                <div style="font-weight: bold; margin-bottom: 10px;">Photographic Image of Packaging</div>
                <div style="display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; align-items: center; min-height: 200px;">
                    @php
                        $photoImages = $ps->imagesByType('image_of_packaging');
                    @endphp
                    @if($photoImages->count() > 0)
                        @foreach($photoImages as $photo)
                            <div style="flex: 0 0 {{ $photoImages->count() == 1 ? '100%' : ($photoImages->count() == 2 ? '48%' : '31%') }}; text-align: center;">
                                <img src="{{ Storage::disk('private')->url($photo->file_path) }}" 
                                     style="max-width: 100%; max-height: {{ $photoImages->count() <= 2 ? '200px' : '120px' }}; object-fit: contain; border: 1px solid #ddd;" 
                                     alt="Packaging Photo">
                            </div>
                        @endforeach
                    @else
                        <div style="text-align: center; color: #999; padding: 40px;">
                            <i class="ti ti-photo" style="font-size: 48px;"></i>
                            <p style="margin-top: 10px;">No packaging image uploaded</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Part Identification Point --}}
            <div style="border: 1px solid #000; padding: 10px; min-height: 180px;">
                <div style="font-weight: bold; margin-bottom: 10px;">Part Identification Point</div>
                <div style="display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; align-items: center; min-height: 130px;">
                    @php
                        $labelImages = $ps->imagesByType('part_identification_point');
                    @endphp
                    @if($labelImages->count() > 0)
                        @foreach($labelImages as $label)
                            <div style="flex: 0 0 {{ $labelImages->count() == 1 ? '100%' : ($labelImages->count() == 2 ? '48%' : '31%') }}; text-align: center;">
                                <img src="{{ Storage::disk('private')->url($label->file_path) }}" 
                                     style="max-width: 100%; max-height: {{ $labelImages->count() <= 2 ? '120px' : '80px' }}; object-fit: contain; border: 1px solid #ddd;" 
                                     alt="Label Photo">
                            </div>
                        @endforeach
                    @else
                        <div style="text-align: center; color: #999; padding: 30px;">
                            <i class="ti ti-tag" style="font-size: 36px;"></i>
                            <p style="margin-top: 8px; font-size: 11px;">No label image uploaded</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- AAIJ Approval Section --}}
    <div style="border: 1px solid #000; padding: 10px;">
        <div style="font-weight: bold; margin-bottom: 10px;">AAIJ Approval</div>
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; font-size: 10px;">
            <div style="border: 1px solid #000; padding: 8px; text-align: center;">
                <div><strong>QA</strong></div>
                <div style="min-height: 40px; margin: 10px 0;">
                    @if($ps->isApproved() && $ps->approved_by_name)
                        {{ $ps->approved_by_name }}
                    @endif
                </div>
                <div>{{ $ps->isApproved() && $ps->approved_at ? $ps->approved_at->format('d/m/y') : '' }}</div>
            </div>
            <div style="border: 1px solid #000; padding: 8px; text-align: center;">
                <div><strong>PPC</strong></div>
                <div style="min-height: 40px; margin: 10px 0;"></div>
                <div></div>
            </div>
            <div style="border: 1px solid #000; padding: 8px; text-align: center;">
                <div><strong>Purchasing</strong></div>
                <div style="min-height: 40px; margin: 10px 0;"></div>
                <div></div>
            </div>
        </div>
    </div>
</div>
