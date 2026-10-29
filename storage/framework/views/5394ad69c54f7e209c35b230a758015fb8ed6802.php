<?php $__env->startSection('title', 'Measurement System Analysis (Gage R&R)'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .msa-table { table-layout: fixed; width: 100%; min-width: 760px; }
    .msa-table th, .msa-table td { text-align: center; vertical-align: middle; white-space: nowrap; padding: 0.25rem; }
    .msa-table th.row-label, .msa-table td.row-label { width: 110px; text-align: left; padding-left: 0.5rem; }
    .msa-table input.form-control-sm { text-align: right; font-size: 0.875rem; padding: 0.25rem 0.4rem; background-color: #e9fbe9; }
    .msa-table tr.calc td { background-color: #f8f9fa; font-size: 0.875rem; }
    .msa-summary span.label { color: #6c757d; margin-right: .25rem; }
    .msa-summary span.val { font-weight: 600; margin-right: 1.25rem; }
    .result-box { border: 1px solid #dee2e6; border-radius: .375rem; padding: .75rem 1rem; height: 100%; }
    .result-box .title { font-size: .8rem; color: #6c757d; }
    .result-box .num { font-size: 1.25rem; font-weight: 600; }
    .card { overflow: hidden; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="mb-1">Measurement System Analysis (Gage R&amp;R)</h3>
            <p class="text-muted mb-0">Metode Average &amp; Range. Isi data pengukuran, hasil perhitungan tampil otomatis dan dapat dicetak ke PDF.</p>
        </div>
    </div>

    <form id="msaForm" method="POST" onsubmit="return false;">
        <?php echo csrf_field(); ?>

        <!-- Section 1: General Information -->
        <div class="card mb-4">
            <div class="card-header bg-light"><h5 class="mb-0">General Information</h5></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="part_no_name" class="form-label">Part No. &amp; Name</label>
                        <input type="text" class="form-control bg-light" id="part_no_name" name="part_no_name" readonly placeholder="Auto-populated">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="date" name="date" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="characteristics" class="form-label">Characteristics <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="characteristics" name="characteristics" required placeholder="e.g., Width">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="performed_by" class="form-label">Performed by <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="performed_by" name="performed_by" required placeholder="Nama pelaksana">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Specifications <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.01" class="form-control" id="spec_nominal" placeholder="Nominal" required style="max-width: 45%;">
                            <span class="input-group-text bg-light" style="border-left: 0; border-right: 0;">±</span>
                            <input type="number" step="0.01" min="0" class="form-control" id="spec_tolerance" placeholder="Tolerance" required style="max-width: 45%;">
                        </div>
                        <input type="hidden" id="specifications" name="specifications">
                        <small class="text-muted">Contoh: 3.5 ± 0.2</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="gage_name" class="form-label">Gage Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="gage_name" name="gage_name" required placeholder="e.g., Caliper">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="gage_no" class="form-label">Gage No</label>
                        <input type="text" class="form-control" id="gage_no" name="gage_no" placeholder="Nomor alat ukur">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="gage_type" class="form-label">Gage Type <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="gage_type" name="gage_type" required placeholder="e.g., Digital">
                    </div>
                </div>
                
                <!-- Appraiser Names Section -->
                <div class="border-top pt-3 mt-3">
                    <h6 class="mb-3 text-muted">Appraiser Names</h6>
                    <div class="row" id="appraiser_names_row">
                        <div class="col-md-4 mb-3" id="appraiser_a_field">
                            <label for="appraiser_a" class="form-label">Appraiser A <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="appraiser_a" name="appraiser_a" required placeholder="Nama Appraiser A">
                        </div>
                        <div class="col-md-4 mb-3" id="appraiser_b_field">
                            <label for="appraiser_b" class="form-label">Appraiser B <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="appraiser_b" name="appraiser_b" required placeholder="Nama Appraiser B">
                        </div>
                        <div class="col-md-4 mb-3" id="appraiser_c_field" style="display: block;">
                            <label for="appraiser_c" class="form-label">Appraiser C <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="appraiser_c" name="appraiser_c" required placeholder="Nama Appraiser C">
                        </div>
                    </div>
                    <small class="text-muted">
                        <i class="ti ti-info-circle me-1"></i> 
                        Nama appraiser akan ditampilkan di dokumen PDF
                    </small>
                </div>
            </div>
        </div>

        <!-- Section 2: Study Settings -->
        <div class="card mb-4">
            <div class="card-header bg-light"><h5 class="mb-0">Study Settings</h5></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="appraisers" class="form-label">Jumlah Appraiser</label>
                        <select id="appraisers" class="form-select" onchange="onSettingsChange()">
                            <option value="2">2 (A, B)</option>
                            <option value="3" selected>3 (A, B, C)</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="trials" class="form-label">Jumlah Trial</label>
                        <select id="trials" class="form-select" onchange="onSettingsChange()">
                            <option value="2">2</option>
                            <option value="3" selected>3</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="parts" class="form-label">Jumlah Part</label>
                        <select id="parts" class="form-select" onchange="onSettingsChange()">
                            <?php for($i = 2; $i <= 10; $i++): ?>
                                <option value="<?php echo e($i); ?>" <?php if($i === 10): echo 'selected'; endif; ?>><?php echo e($i); ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: Data Collection Sheet -->
        <div class="card mb-4">
            <div class="card-header bg-light"><h5 class="mb-0">Data Collection Sheet</h5></div>
            <div class="card-body">
                <div id="measurementContainer"></div>

                <h6 class="mt-4">Part Average</h6>
                <div class="table-responsive">
                    <table class="table table-bordered msa-table mb-2" id="partAvgTable"></table>
                </div>
                <div class="msa-summary" id="sheetSummary"></div>
            </div>
        </div>

        <!-- Section 4: Result -->
        <div class="card mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Hasil Perhitungan</h5>
                <span id="statusBadge" class="badge bg-secondary">Data belum lengkap</span>
            </div>
            <div class="card-body">
                <div id="resultEmpty" class="alert alert-info mb-0">
                    <i class="ti ti-info-circle me-2"></i> Lengkapi seluruh data pengukuran untuk melihat hasil.
                </div>
                <div id="resultGrid" class="row g-3" style="display:none;"></div>
                <div id="resultNote" class="small text-muted mt-3" style="display:none;">
                    %GRR &lt; 10% : dapat diterima &nbsp;|&nbsp; 10–30% : bersyarat &nbsp;|&nbsp; &gt; 30% : tidak dapat diterima &nbsp;|&nbsp; ndc ≥ 5 disarankan.
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-center gap-3 flex-wrap mb-4">
            <button type="button" class="btn btn-lg btn-outline-secondary" id="saveDraftBtn" onclick="saveDraft()" disabled>
                <i class="ti ti-file-check me-2"></i> Save Draft
            </button>
            <button type="button" class="btn btn-lg btn-primary" id="submitBtn" onclick="submitForm()" disabled>
                <i class="ti ti-send me-2"></i> Submit
            </button>
        </div>

        <!-- Status Message -->
        <div class="text-center">
            <div id="formLoadingStatus" class="text-warning">
                <small><i class="ti ti-loader-3"></i> Loading form...</small>
            </div>
            <div id="formStatusSuccess" class="text-success" style="display:none;">
                <small><i class="ti ti-check-circle me-1"></i> Form loaded successfully</small>
            </div>
        </div>
    </form>
</div>

<script>
// ===== KONSTANTA (identik dengan App\Services\GageRRCalculator) =====
const APPRAISERS = ['A', 'B', 'C'];
const K1 = {2: 0.8862, 3: 0.5908};
const K2 = {2: 0.7071, 3: 0.5231};
const K3 = {2: 0.7071, 3: 0.5231, 4: 0.4467, 5: 0.4030, 6: 0.3742, 7: 0.3534, 8: 0.3375, 9: 0.3249, 10: 0.3146};
const D4 = {2: 3.27, 3: 2.58};
const MAX_TRIALS = 3, MAX_PARTS = 10;

// ===== STATE =====
window.msaId = null;
window.settings = {appraisers: 3, trials: 3, parts: 10};
window.measurements = {};
APPRAISERS.forEach(a => {
    window.measurements[a] = Array.from({length: MAX_TRIALS}, () => Array(MAX_PARTS).fill(''));
});

// ===== UTIL =====
const GENERAL_FIELDS = {
    partNoName: 'part_no_name', characteristics: 'characteristics', specifications: 'specifications',
    gageName: 'gage_name', gageNo: 'gage_no', gageType: 'gage_type', date: 'date', performedBy: 'performed_by',
    appraiserA: 'appraiser_a', appraiserB: 'appraiser_b', appraiserC: 'appraiser_c'
};

function fmt(x, d) {
    if (x === null || x === undefined || isNaN(x)) return '-';
    return (Math.abs(x) < Math.pow(10, -d) / 2 ? 0 : x).toFixed(d).replace('.', ',');
}
function num(v) {
    if (v === '' || v === null || v === undefined) return null;
    const n = Number(v);
    return isNaN(n) ? null : n;
}
function activeApps() { return APPRAISERS.slice(0, window.settings.appraisers); }
function csrf() { return document.querySelector('meta[name="csrf-token"]').getAttribute('content'); }

// ===== PERHITUNGAN (cermin dari GageRRCalculator::compute) =====
function compute() {
    const {appraisers: nA, trials: nT, parts: nP} = window.settings;
    const apps = activeApps();
    const out = {avg: {}, rng: {}, xa: {}, ra: {}, partAvg: [], complete: false};
    let filled = true;

    apps.forEach(a => {
        out.avg[a] = []; out.rng[a] = [];
        for (let p = 0; p < nP; p++) {
            const vals = [];
            for (let t = 0; t < nT; t++) {
                const x = num(window.measurements[a][t][p]);
                if (x === null) filled = false; else vals.push(x);
            }
            if (vals.length === nT) {
                out.avg[a][p] = vals.reduce((s, x) => s + x, 0) / nT;
                out.rng[a][p] = Math.max(...vals) - Math.min(...vals);
            }
        }
        const avgs = out.avg[a].filter(x => x !== undefined);
        if (avgs.length === nP) {
            out.xa[a] = avgs.reduce((s, x) => s + x, 0) / nP;
            out.ra[a] = out.rng[a].reduce((s, x) => s + x, 0) / nP;
        }
    });

    for (let p = 0; p < nP; p++) {
        const col = apps.map(a => out.avg[a][p]).filter(x => x !== undefined);
        if (col.length === nA) out.partAvg[p] = col.reduce((s, x) => s + x, 0) / nA;
    }

    out.complete = filled && out.partAvg.filter(x => x !== undefined).length === nP;
    if (!out.complete) return out;

    const xs = apps.map(a => out.xa[a]);
    out.xbar = out.partAvg.reduce((s, x) => s + x, 0) / nP;
    out.rp = Math.max(...out.partAvg) - Math.min(...out.partAvg);
    out.rbb = apps.map(a => out.ra[a]).reduce((s, x) => s + x, 0) / nA;
    out.xdiff = Math.max(...xs) - Math.min(...xs);
    out.ucl = out.rbb * D4[nT];
    out.ev = out.rbb * K1[nT];
    const av2 = Math.pow(out.xdiff * K2[nA], 2) - Math.pow(out.ev, 2) / (nP * nT);
    out.av = av2 > 0 ? Math.sqrt(av2) : 0;
    out.grr = Math.sqrt(out.ev * out.ev + out.av * out.av);
    out.pv = out.rp * K3[nP];
    out.tv = Math.sqrt(out.grr * out.grr + out.pv * out.pv);
    const pct = x => out.tv > 0 ? 100 * x / out.tv : 0;
    out.pev = pct(out.ev); out.pav = pct(out.av); out.pgrr = pct(out.grr); out.ppv = pct(out.pv);
    out.ndc = out.grr > 0 ? 1.41 * out.pv / out.grr : 0;
    return out;
}

// ===== RENDER TABEL PENGUKURAN =====
function renderMeasurementTables() {
    const {trials: nT, parts: nP} = window.settings;
    const container = document.getElementById('measurementContainer');
    container.innerHTML = '';

    // === ALWAYS ADD WARNING FIRST ===
    const warningDiv = document.createElement('div');
    warningDiv.className = 'alert alert-warning d-flex align-items-center mb-3';
    warningDiv.setAttribute('role', 'alert');
    warningDiv.style.cssText = 'display: flex !important;';
    warningDiv.innerHTML = '<i class="ti ti-alert-triangle me-2" style="font-size: 1.5rem;"></i>' +
                           '<strong>Seluruh kolom/isian yang berwarna hijau wajib diisi semua.</strong>';
    container.appendChild(warningDiv);

    activeApps().forEach(a => {
        let head = '<tr><th class="row-label">Appraiser ' + a + '</th>';
        for (let p = 0; p < nP; p++) head += '<th>' + (p + 1) + '</th>';
        head += '</tr>';

        let body = '';
        for (let t = 0; t < nT; t++) {
            body += '<tr><td class="row-label">Trial ' + (t + 1) + '</td>';
            for (let p = 0; p < nP; p++) {
                const val = window.measurements[a][t][p];
                body += '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm" '
                    + 'value="' + (val === '' ? '' : val) + '" data-app="' + a + '" data-trial="' + t + '" data-part="' + p + '" '
                    + 'oninput="onMeasureInput(this)" placeholder="0.00"></td>';
            }
            body += '</tr>';
        }
        body += '<tr class="calc"><td class="row-label">Average</td>';
        for (let p = 0; p < nP; p++) body += '<td id="avg-' + a + '-' + p + '">-</td>';
        body += '</tr><tr class="calc"><td class="row-label">Range</td>';
        for (let p = 0; p < nP; p++) body += '<td id="rng-' + a + '-' + p + '">-</td>';
        body += '</tr>';

        const wrap = document.createElement('div');
        wrap.className = 'mb-4';
        wrap.innerHTML = '<div class="table-responsive"><table class="table table-bordered msa-table mb-1"><thead class="table-light">'
            + head + '</thead><tbody>' + body + '</tbody></table></div>'
            + '<div class="msa-summary" id="summary-' + a + '"></div>';
        container.appendChild(wrap);
    });

    let pa = '<thead class="table-light"><tr><th class="row-label">Part</th>';
    for (let p = 0; p < nP; p++) pa += '<th>' + (p + 1) + '</th>';
    pa += '</tr></thead><tbody class="calc"><tr class="calc"><td class="row-label">Part Average</td>';
    for (let p = 0; p < nP; p++) pa += '<td id="pavg-' + p + '">-</td>';
    pa += '</tr></tbody>';
    document.getElementById('partAvgTable').innerHTML = pa;

    refreshCalculated();
}

function onMeasureInput(el) {
    window.measurements[el.dataset.app][+el.dataset.trial][+el.dataset.part] = el.value;
    refreshCalculated();
}

function onSettingsChange() {
    window.settings = {
        appraisers: +document.getElementById('appraisers').value,
        trials: +document.getElementById('trials').value,
        parts: +document.getElementById('parts').value
    };
    
    // Update appraiser C field based on number of appraisers
    const appraiserCField = document.getElementById('appraiser_c_field');
    const appraiserCInput = document.getElementById('appraiser_c');
    
    if (window.settings.appraisers === 3) {
        // Show Appraiser C field and make it required
        appraiserCField.style.display = 'block';
        appraiserCInput.setAttribute('required', 'required');
    } else {
        // Hide Appraiser C field and make it optional
        appraiserCField.style.display = 'none';
        appraiserCInput.removeAttribute('required');
        appraiserCInput.value = ''; // Clear value when not needed
    }
    
    renderMeasurementTables();
    scheduleAutoSave();
}

// ===== UPDATE NILAI HASIL HITUNG =====
function setText(id, text) { const el = document.getElementById(id); if (el) el.textContent = text; }

function refreshCalculated() {
    const r = compute();
    const nP = window.settings.parts;

    activeApps().forEach(a => {
        for (let p = 0; p < nP; p++) {
            setText('avg-' + a + '-' + p, r.avg[a][p] === undefined ? '-' : fmt(r.avg[a][p], 2));
            setText('rng-' + a + '-' + p, r.rng[a][p] === undefined ? '-' : fmt(r.rng[a][p], 2));
        }
        const s = document.getElementById('summary-' + a);
        if (s) s.innerHTML = '<span class="label">X̄' + a.toLowerCase() + ' =</span><span class="val">' + (r.xa[a] === undefined ? '-' : fmt(r.xa[a], 3)) + '</span>'
            + '<span class="label">R̄' + a.toLowerCase() + ' =</span><span class="val">' + (r.ra[a] === undefined ? '-' : fmt(r.ra[a], 3)) + '</span>';
    });
    for (let p = 0; p < nP; p++) setText('pavg-' + p, r.partAvg[p] === undefined ? '-' : fmt(r.partAvg[p], 2));

    const sum = document.getElementById('sheetSummary');
    sum.innerHTML = r.complete
        ? '<span class="label">X̄ =</span><span class="val">' + fmt(r.xbar, 3) + '</span>'
          + '<span class="label">Rp =</span><span class="val">' + fmt(r.rp, 3) + '</span>'
          + '<span class="label">R̿ =</span><span class="val">' + fmt(r.rbb, 3) + '</span>'
          + '<span class="label">X̄DIFF =</span><span class="val">' + fmt(r.xdiff, 3) + '</span>'
          + '<span class="label">UCL R =</span><span class="val">' + fmt(r.ucl, 3) + '</span>'
        : '';

    renderResult(r);
}

function renderResult(r) {
    const grid = document.getElementById('resultGrid');
    const badge = document.getElementById('statusBadge');
    document.getElementById('resultEmpty').style.display = r.complete ? 'none' : 'block';
    grid.style.display = r.complete ? 'flex' : 'none';
    document.getElementById('resultNote').style.display = r.complete ? 'block' : 'none';

    if (!r.complete) {
        badge.className = 'badge bg-secondary'; badge.textContent = 'Data belum lengkap';
        return;
    }
    const items = [
        ['EV (Repeatability)', fmt(r.ev, 3), '%EV ' + fmt(r.pev, 2)],
        ['AV (Reproducibility)', fmt(r.av, 3), '%AV ' + fmt(r.pav, 2)],
        ['GRR', fmt(r.grr, 3), '%GRR ' + fmt(r.pgrr, 2)],
        ['PV (Part Variation)', fmt(r.pv, 3), '%PV ' + fmt(r.ppv, 2)],
        ['TV (Total Variation)', fmt(r.tv, 3), ''],
        ['ndc', fmt(r.ndc, 2), r.ndc >= 5 ? 'memadai (≥ 5)' : 'kurang (< 5)']
    ];
    grid.innerHTML = items.map(i => '<div class="col-6 col-md-4"><div class="result-box"><div class="title">' + i[0] + '</div>'
        + '<div class="num">' + i[1] + '</div><div class="small text-muted">' + i[2] + '</div></div></div>').join('');

    if (r.pgrr < 10) { badge.className = 'badge bg-success'; badge.textContent = '%GRR ' + fmt(r.pgrr, 2) + ' — Diterima'; }
    else if (r.pgrr <= 30) { badge.className = 'badge bg-warning text-dark'; badge.textContent = '%GRR ' + fmt(r.pgrr, 2) + ' — Bersyarat'; }
    else { badge.className = 'badge bg-danger'; badge.textContent = '%GRR ' + fmt(r.pgrr, 2) + ' — Tidak diterima'; }
}

// ===== LOAD FORM =====
function initializeForm() {
    const token = window.location.pathname.split('/').pop();
    if (!token) { Swal.fire('Error', 'Invalid token', 'error'); return; }

    fetch('/api/msa/form/' + token)
        .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
        .then(data => {
            window.msaId = data.id;
            populateFormData(data);
            ['saveDraftBtn', 'submitBtn'].forEach(id => document.getElementById(id).disabled = false);
            document.getElementById('formLoadingStatus').style.display = 'none';
            document.getElementById('formStatusSuccess').style.display = 'inline';
            setupAutoSave();
        })
        .catch(error => {
            console.error('Error loading form:', error);
            document.getElementById('formLoadingStatus').innerHTML = '<small class="text-danger"><i class="ti ti-alert-circle"></i> Error loading form</small>';
            Swal.fire('Error', 'Failed to load form data: ' + error.message, 'error');
        });
}

function populateFormData(data) {
    const g = data.generalInformation || {};
    Object.keys(GENERAL_FIELDS).forEach(k => {
        if (g[k] !== undefined && g[k] !== null) {
            // Special handling for specifications - parse to nominal and tolerance
            if (k === 'specifications' && g[k]) {
                const parsed = parseSpecification(g[k]);
                if (parsed) {
                    document.getElementById('spec_nominal').value = parsed.nominal;
                    document.getElementById('spec_tolerance').value = parsed.tolerance;
                }
            }
            document.getElementById(GENERAL_FIELDS[k]).value = g[k];
        }
    });

    window.settings = data.settings || {appraisers: 3, trials: 3, parts: 10};
    ['appraisers', 'trials', 'parts'].forEach(k => {
        if (window.settings[k] !== undefined) document.getElementById(k).value = window.settings[k];
    });
    
    // Update appraiser C field visibility based on loaded settings
    const appraiserCField = document.getElementById('appraiser_c_field');
    if (window.settings.appraisers === 3) {
        appraiserCField.style.display = 'block';
    } else {
        appraiserCField.style.display = 'none';
    }

    const meas = data.measurements || {};
    APPRAISERS.forEach(a => {
        if (meas[a]) {
            for (let t = 0; t < MAX_TRIALS && meas[a][t]; t++) {
                for (let p = 0; p < MAX_PARTS && meas[a][t][p] !== undefined; p++) {
                    window.measurements[a][t][p] = meas[a][t][p];
                }
            }
        }
    });

    renderMeasurementTables();
    refreshCalculated();
}

// Parse specification format "3.5±0.2" to {nominal: 3.5, tolerance: 0.2}
function parseSpecification(spec) {
    if (!spec) return null;
    const match = spec.match(/([\d.,]+)\s*[±±]\s*([\d.,]+)/);
    if (!match) return null;
    return {
        nominal: parseFloat(match[1].replace(',', '.')),
        tolerance: parseFloat(match[2].replace(',', '.'))
    };
}

// Update specifications hidden field when nominal or tolerance changes
function updateSpecifications() {
    const nominal = document.getElementById('spec_nominal').value;
    const tolerance = document.getElementById('spec_tolerance').value;
    if (nominal && tolerance) {
        document.getElementById('specifications').value = nominal + '±' + tolerance;
    } else {
        document.getElementById('specifications').value = '';
    }
    scheduleAutoSave();
}

// Setup specifications field listeners
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('spec_nominal').addEventListener('input', updateSpecifications);
    document.getElementById('spec_tolerance').addEventListener('input', updateSpecifications);
});

function collectFormData() {
    const general = {};
    Object.keys(GENERAL_FIELDS).forEach(k => general[k] = document.getElementById(GENERAL_FIELDS[k]).value);

    const {trials: nT, parts: nP} = window.settings;
    const meas = {};
    activeApps().forEach(a => {
        meas[a] = [];
        for (let t = 0; t < nT; t++) meas[a].push(window.measurements[a][t].slice(0, nP).map(v => v === '' ? null : Number(v)));
    });
    return {generalInformation: general, settings: window.settings, measurements: meas};
}

// ===== AUTO SAVE =====
let autoSaveTimer = null;
function scheduleAutoSave() {
    if (!window.msaId) return;
    clearTimeout(autoSaveTimer);
    autoSaveTimer = setTimeout(() => saveDraft(true), 2000);
}
function setupAutoSave() {
    document.getElementById('msaForm').addEventListener('change', scheduleAutoSave);
}

// ===== SAVE / PREVIEW / SUBMIT =====
function post(url, payload) {
    return fetch(url, {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf()},
        body: JSON.stringify(payload)
    }).then(r => r.json().catch(() => ({})).then(body => {
        if (!r.ok) throw new Error(body.message || ('HTTP ' + r.status));
        return body;
    }));
}

function saveDraft(silent = false) {
    if (!window.msaId) {
        if (!silent) Swal.fire('Error', 'Form not fully loaded. Please refresh the page and try again.', 'error');
        return Promise.reject();
    }
    return post('/api/msa/' + window.msaId + '/draft', collectFormData())
        .then(result => {
            if (!result.success) throw new Error(result.message || 'Failed to save draft');
            if (!silent) Swal.fire('Success', 'Draft saved successfully!', 'success');
            return result;
        })
        .catch(error => {
            if (!silent) Swal.fire('Error', 'Failed to save draft: ' + error.message, 'error');
            throw error;
        });
}

function validate() {
    const required = [
        ['date', 'Date'], ['characteristics', 'Characteristics'], ['specifications', 'Specifications'],
        ['gage_name', 'Gage Name'], ['gage_type', 'Gage Type'], ['performed_by', 'Performed by']
    ];
    for (const [id, label] of required) {
        const el = document.getElementById(id);
        if (!el.value.trim()) {
            Swal.fire('Validation Error', label + ' is required', 'warning');
            el.focus();
            return false;
        }
    }
    const {trials: nT, parts: nP} = window.settings;
    for (const a of activeApps()) {
        for (let t = 0; t < nT; t++) for (let p = 0; p < nP; p++) {
            if (num(window.measurements[a][t][p]) === null) {
                Swal.fire('Validation Error', 'Appraiser ' + a + ' - Trial ' + (t + 1) + ' - Part ' + (p + 1) + ' belum diisi', 'warning');
                return false;
            }
        }
    }
    return true;
}

function submitForm() {
    if (!validate()) return;
    if (!window.msaId) {
        Swal.fire('Error', 'Form not fully loaded. Please refresh the page and try again.', 'error');
        return;
    }
    
    // DEBUG: Log data yang akan dikirim
    const formData = collectFormData();
    console.log('=== MSA SUBMIT DEBUG ===');
    console.log('Measurements keys:', Object.keys(formData.measurements));
    console.log('Settings:', formData.settings);
    console.log('Sample A[0]:', formData.measurements.A ? formData.measurements.A[0] : 'not set');
    console.log('Sample B[0]:', formData.measurements.B ? formData.measurements.B[0] : 'not set');
    console.log('Full data:', JSON.stringify(formData, null, 2));
    
    // Show loading
    Swal.fire({
        title: 'Submitting...',
        text: 'Please wait',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });
    
    post('/api/msa/' + window.msaId + '/submit', formData)
        .then(result => {
            Swal.close();
            if (!result.success) { 
                // Show detailed validation errors if available
                let errorMsg = result.message || 'Failed to submit form';
                if (result.errors) {
                    errorMsg += '\n\nDetails:\n';
                    for (const [field, messages] of Object.entries(result.errors)) {
                        errorMsg += '- ' + field + ': ' + (Array.isArray(messages) ? messages.join(', ') : messages) + '\n';
                    }
                }
                if (result.debug) {
                    console.log('Server debug info:', result.debug);
                }
                Swal.fire('Error', errorMsg, 'error'); 
                return; 
            }
            Swal.fire({icon: 'success', title: 'Success!', text: 'MSA Gage R&R submitted successfully', confirmButtonText: 'OK'})
                .then(() => { window.location.href = result.redirect || '/supplier/dashboard'; });
        })
        .catch(error => {
            Swal.close();
            console.error('Submit error:', error);
            Swal.fire('Error', 'Failed to submit form: ' + error.message, 'error');
        });
}

document.addEventListener('DOMContentLoaded', initializeForm);
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.supplier', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\KERJAAN\akebono-approval\resources\views/supplier/msa-gage-rr.blade.php ENDPATH**/ ?>