<!-- ============================================================== -->
<!-- Start Header  -->
<!-- ============================================================== -->
@include('partials/header')
<!-- ============================================================== -->
<!-- End Header  -->
<!-- ============================================================== -->


<!-- ============================================================== -->
<!-- Start Top Bar  -->
<!-- ============================================================== -->
@include('partials/topbar')
<!-- ============================================================== -->
<!-- End Top Bar  -->
<!-- ============================================================== -->


<!-- ============================================================== -->
<!-- Start Side Bar  -->
<!-- ============================================================== -->
@include('partials/sidebar')
<!-- ============================================================== -->
<!-- End Side Bar  -->
<!-- ============================================================== -->

<!-- ============================================================== -->
<!-- Page wrapper  -->
<!-- ============================================================== -->
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-12 d-flex justify-content-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Home</li>
                <li class="breadcrumb-item">Reports</li>
                <li class="breadcrumb-item active">Inventory Report</li>
            </ol>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row ">
                <div class="col-lg-12">
                    <div class="card-body">
                        <div class="row align-items-center mb-1">
                            <div class="col-auto ml-auto">
                                <button class="btn btn-outline-secondary btn-sm clearFilter" type="button">
                                    Clear
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-body filterData">
                            <form id="inv_report" name="inv_report">
                                @csrf
                                <div class="row justify-content-center align-items-center">

                                    <div class="col-md-5">
                                        <div class="form-group row">
                                            <div class="col-md-12">
                                                <div class="form-group has-custom m-b-5">
                                                    <label class="filterlabel">Select Custom Date</label>
                                                    <div class="input-daterange input-group" id="date-range">
                                                        <input type="text" class="form-control" name="start" value="{{ \Carbon\Carbon::now()->startOfMonth()->format('m/d/Y') }}"/>
                                                        <span class="input-group-addon bg-info b-0 text-white">to</span>
                                                        <input type="text" class="form-control" name="end" value="{{ \Carbon\Carbon::now()->format('m/d/Y') }}"/>
                                                    </div>
                                                    {{-- <input class="form-control input-daterange-datepicker" id="ir_daterange" type="text" name="ir_daterange" value="{{ \Carbon\Carbon::now()->startOfMonth()->format('m/d/Y') }} - {{ \Carbon\Carbon::now()->format('m/d/Y') }}" />  --}}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-7">
                                        <div class="form-group row">
                                            <div class="col-md-12">
                                                <div class="form-group has-custom m-b-5">
                                                    <label class="filterlabel">Sites</label>
                                                    <select class="form-control selectpicker p-0" multiple id="ir_site" name="ir_site[]" data-style="form-control btn-secondary">
                                                        <option selected value="0101"> All Sites</option>
                                                        @foreach ($Sites as $Site)
                                                            <option value="{{ $Site['id'] }}"> {{ $Site['name'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-5">
                                        <div class="form-group row">
                                            <div class="col-md-12">
                                                <div class="form-group has-custom m-b-5">
                                                    <label class="filterlabel">Transaction Types</label>
                                                    <select class="form-control selectpicker p-0" multiple id="ir_transactiontype" name="ir_transactiontype[]" data-style="form-control btn-secondary">
                                                        <option selected value="0101" >Select All</option>
                                                        @foreach ($TransactionTypes as $TransactionType)
                                                        <option value="{{ $TransactionType['id'] }}"> {{ $TransactionType['name'] }}</option>
                                                        @endforeach                                                
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-7">
                                        <div class="form-group row">
                                            <div class="col-md-12">
                                                <div class="form-group has-custom m-b-5">
                                                    <label class="filterlabel">Item Generic</label>
                                                    <select class="form-control selectpicker p-0" multiple id="ir_generic" name="ir_generic[]" data-style="form-control btn-secondary">
                                                        <option selected value="0101" >Select All</option>
                                                        @foreach ($Generics as $Generic)
                                                        <option value="{{ $Generic['id'] }}"> {{ $Generic['name'] }}</option>
                                                        @endforeach                                                
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- <div class="col-md-4">
                                        <div class="form-group row">
                                            <div class="col-md-12">
                                                <div class="form-group has-custom m-b-5">
                                                    <label class="filterlabel">Item Generic</label>
                                                    <select class="form-control selecter p-0" id="ir_generic" style="color:#222d32">
                                                        <option selected disabled >Select Item Generic</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div> --}}

                                    {{-- 
                                    <div class="col-md-4">
                                        <div class="form-group row">
                                            <div class="col-md-12">
                                                <div class="form-group has-custom m-b-5">
                                                    <label class="filterlabel">Item Category</label>
                                                    <select class="form-control selecter p-0" id="ir_cat" style="color:#222d32">
                                                        <option selected disabled >Select Category</option>
                                                        @foreach ($Categories as $Category)
                                                        <option value="{{ $Category['id'] }}"> {{ $Category['name'] }}</option>
                                                        @endforeach                                                
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group row">
                                            <div class="col-md-12">
                                                <div class="form-group has-custom m-b-5">
                                                    <label class="filterlabel">Item SubCategory</label>
                                                    <select class="form-control selecter p-0" id="ir_subcat" style="color:#222d32">
                                                        <option selected disabled >Select SubCategory</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group row">
                                            <div class="col-md-12">
                                                <div class="form-group has-custom m-b-5">
                                                    <label class="filterlabel">Item Type</label>
                                                    <select class="form-control selecter p-0" id="ir_type" style="color:#222d32">
                                                        <option selected disabled >Select Item Type</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                  

                                    <div class="col-md-4">
                                        <div class="form-group row">
                                            <div class="col-md-12">
                                                <div class="form-group has-custom m-b-5">
                                                    <label class="filterlabel">Item Brand</label>
                                                    <select class="form-control selecter p-0" id="ir_brand" style="color:#222d32">
                                                        <option selected disabled >Select Item Brand</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div> --}}

                            
                                </div>

                                <!-- Submit Button -->
                                <div class="row mt-3">
                                    <div class="col-11 text-center">
                                        <button type="submit" class="btn btn-primary p-2">
                                            <i class="mdi mdi-file-document"></i> Get Inventory Report
                                        </button>
                                    </div>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- Start Footer  -->
<!-- ============================================================== -->
@include('partials/footer')
<!-- ============================================================== -->
<!-- End Footer  -->
<!-- ============================================================== -->

<script src="{{ asset('assets/custom/inventory_report.js') }}"></script>