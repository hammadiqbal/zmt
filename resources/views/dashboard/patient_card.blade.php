@include('partials/header')
@include('partials/topbar')
@include('partials/sidebar')

<style>

    .card-center-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 70vh;
        flex-direction: column;
        gap: 15px;
    }


    .card-wrapper {
        width: 4in;
        /* height: 2.75in; */
        font-family: Arial, sans-serif;
        border: 1px solid #ccc;
        overflow: hidden;
        position: relative;
        box-shadow: 0 0 5px rgba(0,0,0,0.15);
    }
</style>
<style>
/* ---------- PRINT LAYOUT ---------- */
@media print {

    /* 1️⃣  Hide absolutely everything */
    body * {
        visibility: hidden !important;
    }

    /* 2️⃣  …except the element we actually want to print */
    .printable-card,
    .printable-card * {
        visibility: visible !important;
    }

    /* 3️⃣  Position the card at the top-left of the printed page */
    .printable-card {
        position: absolute;
        inset: 0;              /* top:0; right:0; bottom:0; left:0 */
        margin: auto;          /* centres it on the sheet */
        /* optional: remove any box-shadow/border for clean edges   */
        box-shadow: none !important;
        border: none !important;
    }

    /* 4️⃣  Hide action buttons */
    .no-print {
        display: none !important;
    }
}
</style>
<!-- ✅ Card + Buttons Centered -->
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-12 d-flex justify-content-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Home</li>
                <li class="breadcrumb-item active">Patient Card</li>
            </ol>
        </div>
    </div>
    <div class="card-center-wrapper">
        <div class="card-wrapper printable-card">
            <!-- Header -->
            <div style="background-color: #0e254f; color: white; padding: 6px 12px; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center;">
                    <img src="{{ asset('assets/logo.webp') }}" alt="ZMT Logo" style="height: 30px; margin-right: 8px;">
                </div>
                <div style="font-size: 13px;">Patient Registration</div>
            </div>

            <!-- Body -->
            <div style="font-size: 11.5px; padding: 8px 12px; background-color: #f4faff; background: url('{{ asset('images/bg-shape.png') }}') no-repeat right bottom / contain;">
                <div style="position: absolute; top: 52px; right: 15px;">
                    {{-- <img src="{{ asset('assets/qr.svg') }}" alt="QR Code" style="height: 45px;"> --}}
                    <img src="https://api.qrserver.com/v1/create-qr-code/?data={{ urlencode($patient->mr_code) }}&size=80x80" alt="QR Code" style="height: 45px;">

                </div>
                <table style="width: 100%; font-size: 11.5px;">
                    <tr>
                        <td style="font-weight: 700; ">MR #:</td>
                        <td>{{ $patient->mr_code }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 700;">Name:</td>
                        <td>{{ ucwords($patient->name) }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 700;">F/H Name:</td>
                        <td>{{ ucwords($patient->guardian) }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 700;">Gender:</td>
                        <td>{{ ucwords($patient->genderName) }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 700;">DOB:</td>
                        <td>{{ $patient->dob ? \Carbon\Carbon::createFromTimestamp($patient->dob)->format('d-m-Y') : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 700;">Cell No:</td>
                        <td>{{ $patient->mobile_no ?? 'N/A' }}</td>
                    </tr>
                </table>

                {{-- <div><span style="color:black;font-weight: 700;">MR #:</span><span style="margin-left: 8px;"> {{ $patient->mr_code }}</span></div>
                <div><span style="color:black;font-weight: 700;">Name:</span><span style="margin-left: 8px;"> {{ ucwords($patient->name) }} </span></div>
                <div><span style="color:black;font-weight: 700;">F/H Name:</span> <span style="margin-left: 8px;">{{ ucwords($patient->guardian) }} </span></div>

                <div>
                    <span style="color:black;font-weight: 700;">Gender:</span>
                    <span style="margin-left: 8px;"> {{ ucwords($patient->genderName) }}</span>
                </div>

                <div><span style="color:black;font-weight: 700;">DOB:</span> {{ $patient->dob ? \Carbon\Carbon::createFromTimestamp($patient->dob)->format('d-m-Y') : 'N/A' }}</div>
                <div><span style="color:black;font-weight: 700;">Cell No:</span> {{ $patient->mobile_no ?? 'N/A' }}</div>
            --}}
            </div>

            <!-- Footer -->
            <div style="background-color: #0e254f; color: white; font-size: 9px; padding: 6px 6px; display: flex; justify-content: space-between; align-items: start;">
                <div style="max-width: 70%;">
                    <i class="mdi mdi-map-marker" style="font-size: 12px; vertical-align: middle;"></i> ZMT FL-6/6, Block 4 Gulshan-e-Iqbal, Karachi, Sindh<br>
                    <i class="mdi mdi-phone" style="font-size: 12px; vertical-align: middle;"></i> Tel: +92-21-34981242 / +92-323-2115770
                </div>
                <div style="text-align: right; position: relative;">
                    <span style="color: white; text-decoration: none;"><i class="mdi mdi-email" style="font-size: 12px; vertical-align: middle;"></i> info@zmtclinics.org</span><br>
                    <span style="color: white; text-decoration: none;"><i class="mdi mdi-web" style="font-size: 12px; vertical-align: middle;"></i> https://www.zmtclinics.org</span>
                </div>
            </div>

            
        </div>

        <!-- Buttons Immediately Below Card -->
        <div class="text-center no-print">
            <a href="javascript:void(0);" onclick="window.print()" class="btn btn-primary me-2">
                <i class="fa fa-print"></i> Print Card
            </a>
            {{-- <a href="#" class="btn btn-info text-white">
                <i class="fa fa-download"></i> Download Card
            </a> --}}
        </div>
    </div>
</div>
@include('partials/footer')
