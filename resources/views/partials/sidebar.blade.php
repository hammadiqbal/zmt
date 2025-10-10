        <!-- ============================================================== -->
        <!-- Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->

        <aside class="left-sidebar" style="margin-top:10px">
            <!-- Sidebar scroll-->
            <div class="scroll-sidebar">

                <!-- End User profile text-->
                <!-- Sidebar navigation-->
                <nav class="sidebar-nav">
                    <ul id="sidebarnav">
                        <li> <a class="waves-effect waves-dark" href="{{ route('dashboard') }}" aria-expanded="false"><i class="mdi mdi-gauge"></i><span class="hide-menu">Dashboard</span></a>
                    @php
                            $hideUserSetup = true;
                            $hideUserRoles = true;
                
                            $userSetupValues = explode(',', $rights->user_setup);
                            if (in_array('1', $userSetupValues)) {
                                $hideUserSetup = false;
                            }
            
                            $userRolesValues = explode(',', $rights->user_roles);
                            if (in_array('1', $userRolesValues)) {
                                $hideUserRoles = false;
                            }
                    @endphp

                    @if (!$hideUserSetup || !$hideUserRoles)
                        <li> <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false"><i class="mdi mdi-worker"></i><span class="hide-menu">Users</span></a>
                            <ul aria-expanded="false" class="collapse">
                                @if (!$hideUserSetup)
                                <li><a href="{{ route('user') }}">User Setup </a></li>
                                @endif

                                @if (!$hideUserRoles)
                                <li><a href="{{ route('user-roles') }}">User Roles</a></li>
                                @endif
                            </ul>
                        </li>
                    @endif

                    @php
                        $hideProvince = true;
                        $hideDivisions = true;
                        $hideDistricts = true;
                        $hideProvinceValues = explode(',', $rights->province);
                        if (in_array('1', $hideProvinceValues)) {
                            $hideProvince = false;
                        }
        
                        $hideDivisionsValues = explode(',', $rights->divisions);
                        if (in_array('1', $hideDivisionsValues)) {
                            $hideDivisions = false;
                        }

                        $hideDistrictsValues = explode(',', $rights->districts);
                        if (in_array('1', $hideDistrictsValues)) {
                            $hideDistricts = false;
                        }
                    @endphp

                    @if (!$hideProvince || !$hideDivisions || !$hideDistricts)
                        <li> <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false"><i class="mdi mdi-map-marker-multiple"></i><span class="hide-menu">Territories</span></a>
                            <ul aria-expanded="false" class="collapse">
                                    @if (!$hideProvince)
                                    <li><a href="{{ route('province') }}">Provinces</a></li>
                                    @endif

                                    @if (!$hideDivisions)
                                    <li><a href="{{ route('division') }}">Divisions</a></li>
                                    @endif

                                    @if (!$hideDistricts)
                                    <li><a href="{{ route('district') }}">Districts</a></li>
                                    @endif

                            </ul>
                        </li>
                    @endif

                    @php
                        $hideServiceMode = true;
                        $hideServiceType = true;
                        $hideServiceUnit = true;
                        $hideServiceGroups = true;
                        $hideServiceCodeDirectory = true;

                        $hideServiceModeValues = explode(',', $rights->service_modes);
                        if (in_array('1', $hideServiceModeValues)) {
                            $hideServiceMode = false;
                        }
        
                        $hideServiceTypeValues = explode(',', $rights->service_types);
                        if (in_array('1', $hideServiceTypeValues)) {
                            $hideServiceType = false;
                        }
                        $hideServiceUnitValues = explode(',', $rights->service_units);
                        if (in_array('1', $hideServiceUnitValues)) {
                            $hideServiceUnit = false;
                        }

                        $hideServiceGroupsValues = explode(',', $rights->service_groups);
                        if (in_array('1', $hideServiceGroupsValues)) {
                            $hideServiceGroups = false;
                        }

                        $hideServiceCodeDirectoryValues = explode(',', $rights->service_code_directory_setup);
                        if (in_array('1', $hideServiceCodeDirectoryValues)) {
                            $hideServiceCodeDirectory = false;
                        }
                    @endphp

                    @if (!$hideServiceMode || !$hideServiceType || !$hideServiceUnit || !$hideServiceGroups || !$hideServiceCodeDirectory)
                        <li> <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false"><i class="mdi mdi-sync-off"></i><span class="hide-menu">Services</span></a>
                            <ul aria-expanded="false" class="collapse">
                                    @if (!$hideServiceMode)
                                    <li><a href="{{ route('service-mode') }}">Service Modes</a></li>
                                    @endif
                                    @if (!$hideServiceType)
                                    <li><a href="{{ route('service-type') }}">Service Types</a></li>
                                    @endif 
                                    @if (!$hideServiceUnit)
                                    <li><a href="{{ route('service-unit') }}">Service Units</a></li>
                                    @endif 
                                    @if (!$hideServiceGroups)
                                    <li><a href="{{ route('service-group') }}">Service Groups</a></li>
                                    @endif
                                    @if (!$hideServiceCodeDirectory)
                                    <li><a href="{{ route('services') }}">Service Code Directory Setup</a></li>
                                    @endif
                            </ul>
                        </li>
                    @endif

                    @php
                        $hideCCType = true;
                        $hideCCSetup = true;

                        $hideCCTypeValues = explode(',', $rights->cost_center_types);
                        if (in_array('1', $hideCCTypeValues)) {
                            $hideCCType = false;
                        }
        
                        $hideCCSetupalues = explode(',', $rights->cost_center_setup);
                        if (in_array('1', $hideCCSetupalues)) {
                            $hideCCSetup = false;
                        }

                    @endphp

                    @if (!$hideCCType || !$hideCCSetup)
                        <li> <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false"><i class="mdi mdi-tune-vertical"></i><span class="hide-menu">Cost Centers</span></a>
                            <ul aria-expanded="false" class="collapse">
                                    @if (!$hideCCType)
                                    <li><a href="{{ route('cost-center-type') }}">Cost Center Types</a></li>
                                    @endif

                                    @if (!$hideCCSetup)
                                    <li><a href="{{ route('cost-center') }}">Cost Center Setup</a></li>
                                    @endif
                            </ul>
                        </li>
                    @endif

                    @php
                    $hideServiceLocation = true;

                    $hideServiceLocationValues = explode(',', $rights->service_location_setup);
                    if (in_array('1', $hideServiceLocationValues)) {
                        $hideServiceLocation = false;
                    }
                    @endphp

                    @if (!$hideServiceLocation)
                        <li> <a class="waves-effect waves-dark" href="{{ route('service-location') }}" ><i class="mdi mdi-crosshairs-gps"></i>Service Location Setup</a>
                        </li>
                    @endif

                    @php
                        $hideKPIGroup = true;
                        $hideKPIDimension = true;
                        $hideKPIType = true;
                        $hideKPISetup = true;

                        $hideKPIGroupValues = explode(',', $rights->kpi_group);
                        if (in_array('1', $hideKPIGroupValues)) {
                            $hideKPIGroup = false;
                        }
                        $hideKPIDimensionValues = explode(',', $rights->kpi_dimension);
                        if (in_array('1', $hideKPIDimensionValues)) {
                            $hideKPIDimension = false;
                        }
                        $hideKPITypeValues = explode(',', $rights->kpi_types);
                        if (in_array('1', $hideKPITypeValues)) {
                            $hideKPIType = false;
                        }
                        $hideKPISetupValues = explode(',', $rights->kpi_setup);
                        if (in_array('1', $hideKPISetupValues)) {
                            $hideKPISetup = false;
                        }
                    @endphp

                    @if (!$hideKPIGroup || !$hideKPIDimension || !$hideKPIType || !$hideKPISetup)
                        <li> <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false"><i class="mdi mdi-account-key"></i><span class="hide-menu">Key Performance Indicators</span></a>
                            <ul aria-expanded="false" class="collapse">
                                    @if (!$hideKPIGroup)
                                    <li><a href="{{ route('kpi-group') }}">KPI Groups</a></li>
                                    @endif

                                    @if (!$hideKPIDimension)
                                    <li><a href="{{ route('kpi-dimension') }}">KPI Dimensions</a></li>
                                    @endif

                                    @if (!$hideKPIType)
                                    <li><a href="{{ route('kpi-type') }}">KPI Types</a></li>
                                    @endif

                                    @if (!$hideKPISetup)
                                    <li><a href="{{ route('kpi') }}">KPI Setup</a></li>
                                    @endif
                            </ul>
                        </li>
                    @endif


                    @php
                        $hideOrganization = true;
                        $hideSite = true;
                        $hidereferralSite = true;

                        $hideOrganizationValues = explode(',', $rights->organization_setup);
                        if (in_array('1', $hideOrganizationValues)) {
                            $hideOrganization = false;
                        }
                        $hideSiteValues = explode(',', $rights->site_setup);
                        if (in_array('1', $hideSiteValues)) {
                            $hideSite = false;
                        }
                        $hidereferralSiteValues = explode(',', $rights->referral_site);
                        if (in_array('1', $hidereferralSiteValues)) {
                            $hidereferralSite = false;
                        }
                    @endphp

                    @if (!$hideOrganization || !$hideSite)
                        <li> <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false"><i class="mdi mdi-hospital-building"></i><span class="hide-menu">Organization</span></a>
                            <ul aria-expanded="false" class="collapse">
                                @if (!$hideOrganization)
                                <li><a href="{{ route('orgsetup') }}">Organization Setup</a></li>
                                @endif

                                @if (!$hideSite)
                                <li><a href="{{ route('site-setup') }}">Site Setup</a></li>
                                @endif

                                @if (!$hidereferralSite)
                                <li><a href="{{ route('referral-setup') }}">Referral Site Setup</a></li>
                                @endif
                            </ul>
                        </li>
                    @endif

                    @php
                        $hideCCActivation = true;
                        $hideServiceActivation = true;
                        $hideKPIActivation = true;
                        $hideServiceRequisition = true;
                        $hideProcedureCoding = true;
                        $hideServiceLocationActivation = true;

                        $hideCCActivationValues = explode(',', $rights->cost_center_activation);
                        if (in_array('1', $hideCCActivationValues)) {
                            $hideCCActivation = false;
                        }
                        $hideServiceActivationValues = explode(',', $rights->service_activation);
                        if (in_array('1', $hideServiceActivationValues)) {
                            $hideServiceActivation = false;
                        }
                        $hideKPIActivationValues = explode(',', $rights->kpi_activation);
                        if (in_array('1', $hideKPIActivationValues)) {
                            $hideKPIActivation = false;
                        }
                        $hideServiceRequisitionValues = explode(',', $rights->service_requisition_setup);
                        if (in_array('1', $hideServiceRequisitionValues)) {
                            $hideServiceRequisition = false;
                        }
                        $hideProcedureCodingValues = explode(',', $rights->procedure_coding);
                        if (in_array('1', $hideProcedureCodingValues)) {
                            $hideProcedureCoding = false;
                        }
                        $hideServiceLocationActivationValues = explode(',', $rights->service_location_activation);
                        if (in_array('1', $hideServiceLocationActivationValues)) {
                            $hideServiceLocationActivation = false;
                        }
                    @endphp
                    @if (!$hideCCActivation || !$hideServiceActivation || !$hideKPIActivation || !$hideProcedureCoding || !$hideServiceLocationActivation || !$hideServiceRequisition)
                        <li> <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false"><i class="mdi mdi-radioactive"></i><span class="hide-menu">Activations</span></a>
                            <ul aria-expanded="false" class="collapse">
                                    @if (!$hideCCActivation)
                                    <li><a href="{{ route('cc-activation') }}">Cost Centers Activation</a></li>
                                    @endif

                                    @if (!$hideServiceActivation)
                                    <li><a href="{{ route('service-activation') }}">Services Activation</a></li>
                                    @endif

                                    @if (!$hideKPIActivation)
                                    <li><a href="{{ route('kpi-activation') }}">KPI Activation</a></li>
                                    @endif
                                    @if (!$hideServiceRequisition)
                                        <li><a href="{{ route('service-requisition-setup') }}">Service Requisition Setup</a></li>
                                    @endif
                                    @if (!$hideProcedureCoding)
                                        <li><a href="{{ route('procedure-coding') }}">Procedure Coding</a></li>
                                    @endif
                                    @if (!$hideServiceLocationActivation)
                                    <li><a href="{{ route('service-location-activation') }}">Service Location Activation</a></li>
                                    @endif
                            </ul>
                        </li>
                    @endif

                    @php
                        $hideGender = true;
                        $hidePrefix = true;
                        $hideEmpStatus = true;
                        $hideEmpWorkingStatus = true;
                        $hideQuaificationLevel = true;
                        $hideCadre = true;
                        $hidePosition = true;
                        $hideEmployee = true;
                        $hideEmpQualificationSetup = true;
                        $hideEmpDocumentsSetup = true;
                        $hideEmpMedicalLicense = true;
                        $hideEmpSalarySetup = true;
                        $hideEmpCCAllocation = true;
                        $hideEmpServiceAllocation = true;
                        $hideEmpLocationAllocation = true;

                        $hideGenderValues = explode(',', $rights->gender_setup);
                        if (in_array('1', $hideGenderValues)) {
                            $hideGender = false;
                        }
                        $hidePrefixValues = explode(',', $rights->prefix_setup);
                        if (in_array('1', $hidePrefixValues)) {
                            $hidePrefix = false;
                        }
                        $hideEmpStatusValues = explode(',', $rights->employee_status_setup);
                        if (in_array('1', $hideEmpStatusValues)) {
                            $hideEmpStatus = false;
                        }
                        $hideEmpWorkingStatusValues = explode(',', $rights->employee_working_status_setup);
                        if (in_array('1', $hideEmpWorkingStatusValues)) {
                            $hideEmpWorkingStatus = false;
                        }
                        $hideQuaificationLevelValues = explode(',', $rights->qualification_level_setup);
                        if (in_array('1', $hideQuaificationLevelValues)) {
                            $hideQuaificationLevel = false;
                        }
                        $hideCadreValues = explode(',', $rights->cadre_setup);
                        if (in_array('1', $hideCadreValues)) {
                            $hideCadre = false;
                        }
                        $hidePositionValues = explode(',', $rights->position_setup);
                        if (in_array('1', $hidePositionValues)) {
                            $hidePosition = false;
                        }
                        $hideEmployeeValues = explode(',', $rights->employee_setup);
                        if (in_array('1', $hideEmployeeValues)) {
                            $hideEmployee = false;
                        }
                        $hideEmpQualificationSetupValues = explode(',', $rights->employee_qualification_setup);
                        if (in_array('1', $hideEmpQualificationSetupValues)) {
                            $hideEmpQualificationSetup = false;
                        }
                        $hideEmpDocumentsSetupValues = explode(',', $rights->employee_documents);
                        if (in_array('1', $hideEmpDocumentsSetupValues)) {
                            $hideEmpDocumentsSetup = false;
                        }
                        $hideEmpMedicalLicenseValues = explode(',', $rights->employee_medical_license_setup);
                        if (in_array('1', $hideEmpMedicalLicenseValues)) {
                            $hideEmpMedicalLicense = false;
                        }
                        $hideEmpSalarySetupValues = explode(',', $rights->employee_salary_setup);
                        if (in_array('1', $hideEmpSalarySetupValues)) {
                            $hideEmpSalarySetup = false;
                        }
                        $hideEmpCCAllocationValues = explode(',', $rights->employee_cost_center_allocation);
                        if (in_array('1', $hideEmpCCAllocationValues)) {
                            $hideEmpCCAllocation = false;
                        }
                        $hideEmpServiceAllocationValues = explode(',', $rights->employee_services_allocation);
                        if (in_array('1', $hideEmpServiceAllocationValues)) {
                            $hideEmpServiceAllocation = false;
                        }
                        $hideEmpLocationAllocationValues = explode(',', $rights->employee_inventory_location_allocation);
                        if (in_array('1', $hideEmpLocationAllocationValues)) {
                            $hideEmpLocationAllocation = false;
                        }
                    @endphp
                    @if ( !$hideGender || !$hidePrefix || !$hideEmpStatus || !$hideEmpWorkingStatus || !$hideQuaificationLevel || !$hideCadre || !$hidePosition || !$hideEmployee || !$hideEmpQualificationSetup || !$hideEmpDocumentsSetupValues || !$hideEmpMedicalLicense || !$hideEmpSalarySetup || !$hideEmpCCAllocation || !$hideEmpServiceAllocation)
                        <li> <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false"><i class="mdi mdi-human"></i><span class="hide-menu">Human Resource</span></a>
                            <ul aria-expanded="false" class="collapse">
                                @if (!$hideGender)
                                <li><a href="{{ route('employee-gender') }}">Gender Setup</a></li>
                                @endif
                                @if (!$hidePrefix)
                                <li><a href="{{ route('prefix-setup') }}">Prefix Setup</a></li>
                                @endif
                                @if (!$hideEmpStatus)
                                    <li><a href="{{ route('employee-status') }}">Employee Status Setup</a></li>
                                @endif
                                @if (!$hideEmpWorkingStatus)
                                    <li><a href="{{ route('working-status') }}">Employee Working Status Setup</a></li>
                                @endif
                                @if (!$hideQuaificationLevel)
                                    <li><a href="{{ route('emp-qualification-level') }}">Qualification Level Setup</a></li>
                                @endif
                                @if (!$hideCadre)
                                    <li><a href="{{ route('emp-cadre') }}">Cadre Setup</a></li>
                                @endif
                                @if (!$hidePosition)
                                    <li><a href="{{ route('emp-position') }}">Position Setup</a></li>
                                @endif
                                @if (!$hideEmployee)
                                    <li><a href="{{ route('employee') }}">Employee Setup</a></li>
                                @endif
                                @if (!$hideEmpQualificationSetup)
                                    <li><a href="{{ route('emp-qualification-setup') }}">Employee Qualification Setup</a></li>
                                @endif
                                @if (!$hideEmpDocumentsSetup)
                                <li><a href="{{ route('emp-documents') }}">Employee Documents</a></li>
                                @endif
                                @if (!$hideEmpMedicalLicense)
                                    <li><a href="{{ route('emp-medical-license') }}">Employee Medical Licence Setup</a></li>
                                @endif
                                
                                @if (!$hideEmpSalarySetup)
                                    <li><a href="{{ route('emp-salary') }}">Employee Salary Setup</a></li>
                                @endif
                                @if (!$hideEmpCCAllocation)
                                    <li><a href="{{ route('emp-costcenter') }}">Employee Cost Center Allocation</a></li>
                                @endif
                                @if (!$hideEmpServiceAllocation)
                                    <li><a href="{{ route('emp-serviceallocation') }}">Employee Services Allocation</a></li>
                                @endif
                                @if (!$hideEmpLocationAllocation)
                                <li><a href="{{ route('emp-locationallocation') }}">Employee Inventory Location Allocation</a></li>
                                @endif
                            </ul>
                        </li>
                    @endif

                    @php
                    $hideServiceLocationScheduling = true;
                    $hidePatientRegistration = true;
                    $hideServiceBookingPatient = true;
                    $hidePatientArrivalDeparture = true;
                    $hideOutsourcedServices = true;
                    $hidePatientWelfare = true;

                    $hideServiceLocationSchedulingValues = explode(',', $rights->service_location_scheduling);
                    if (in_array('1', $hideServiceLocationSchedulingValues)) {
                        $hideServiceLocationScheduling = false;
                    }
                    $hidePatientRegistrationValues = explode(',', $rights->patient_registration);
                    if (in_array('1', $hidePatientRegistrationValues)) {
                        $hidePatientRegistration = false;
                    }
                    $hideServiceBookingPatientValues = explode(',', $rights->services_booking_for_patients);
                    if (in_array('1', $hideServiceBookingPatientValues)) {
                        $hideServiceBookingPatient = false;
                    }
                    $hidePatientArrivalDepartureValues = explode(',', $rights->patient_arrival_and_departure);
                    if (in_array('1', $hidePatientArrivalDepartureValues)) {
                        $hidePatientArrivalDeparture = false;
                    }
                    $hideOutsourcedServicesValues = explode(',', $rights->outsourced_services);
                    if (in_array('1', $hideOutsourcedServicesValues)) {
                        $hideOutsourcedServices = false;
                    }
                    $hidePatientWelfareValues = explode(',', $rights->patient_welfare);
                    if (in_array('1', $hidePatientWelfareValues)) {
                        $hidePatientWelfare = false;
                    }
                    @endphp

                    @if (!$hideServiceLocationScheduling || !$hidePatientRegistration || !$hideServiceBookingPatient || !$hidePatientArrivalDeparture || !$hideOutsourcedServices || !$hidePatientWelfare)
                        <li> <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false"><i class="mdi mdi-crosshairs-gps"></i><span class="hide-menu">Front Desk Services</span></a>
                            <ul aria-expanded="false" class="collapse">
                            
                                @if (!$hideServiceLocationScheduling)
                                    <li><a href="{{ route('service-location-scheduling') }}">Service Location Scheduling</a></li>
                                @endif
                            
                                @if (!$hidePatientRegistration)
                                    <li><a href="{{ route('patient-registration') }}">Patient Registration</a></li>
                                @endif
                                @if (!$hideServiceBookingPatient)
                                    <li><a href="{{ route('service-booking') }}">Services Booking For Patients</a></li>
                                @endif
                                @if (!$hidePatientArrivalDeparture)
                                    <li><a href="{{ route('patient-inout') }}">Patient Arrival & Departure</a></li>
                                @endif
                                  @if (!$hideOutsourcedServices)
                                    <li><a href="{{ route('outsourced-services') }}">Outsourced Services</a></li>
                                @endif
                                @if (!$hidePatientWelfare)
                                    <li><a href="#">Patient Welfare</a></li>
                                @endif
                            </ul>
                        </li>
                    @endif

                    @php
                    // $hidePysicalAssesmet = true;
                    // $hideNursingAssessment = true;
                    // $hideCPOEMedication = true;
                    // $hideCPOEInvestigation = true;
                    // $hideCPOEProcedure = true;
                    // $hideCPOEServices = true;
                    // $hideLabSampleConfirm = true;
                    // $hideImagingConfirm = true;
                    // $hideLabReporting = true;
                    // $hideImagingReporting = true;
                    $hideMedicalCoding = true;
                    $hideVitalSign = true;
                    $hideEncounterProcedure = true;
                    $hideInvestigationTracking = true;

                    $hideInvestigationTrackingValues = explode(',', $rights->investigation_tracking);
                    if (in_array('1', $hideInvestigationTrackingValues)) {
                        $hideInvestigationTracking = false;
                    }

                    // $hidePysicalAssesmetValues = explode(',', $rights->physician_assessment);
                    // if (in_array('1', $hidePysicalAssesmetValues)) {
                    //     $hidePysicalAssesmet = false;
                    // }
                    // $hideNursingAssessmentValues = explode(',', $rights->nursing_assessment);
                    // if (in_array('1', $hideNursingAssessmentValues)) {
                    //     $hideNursingAssessment = false;
                    // }
                    // $hideCPOEMedicationValues = explode(',', $rights->cpoe_for_medications);
                    // if (in_array('1', $hideCPOEMedicationValues)) {
                    //     $hideCPOEMedication = false;
                    // }
                    // $hideCPOEInvestigationValues = explode(',', $rights->cpoe_for_investigations);
                    // if (in_array('1', $hideCPOEInvestigationValues)) {
                    //     $hideCPOEInvestigation = false;
                    // }
                    // $hideCPOEProcedureValues = explode(',', $rights->cpoe_for_procedures);
                    // if (in_array('1', $hideCPOEProcedureValues)) {
                    //     $hideCPOEProcedure = false;
                    // }
                    // $hideCPOEServicesValues = explode(',', $rights->cpoe_for_services);
                    // if (in_array('1', $hideCPOEServicesValues)) {
                    //     $hideCPOEServices = false;
                    // }
                    // $hideLabSampleConfirmValues = explode(',', $rights->lab_sample_confirmation);
                    // if (in_array('1', $hideLabSampleConfirmValues)) {
                    //     $hideLabSampleConfirm = false;
                    // }
                    // $hideImagingConfirmValues = explode(',', $rights->imaging_confirmation);
                    // if (in_array('1', $hideImagingConfirmValues)) {
                    //     $hideImagingConfirm = false;
                    // }
                    // $hideLabReportingValues = explode(',', $rights->lab_reporting);
                    // if (in_array('1', $hideLabReportingValues)) {
                    //     $hideLabReporting = false;
                    // }
                    // $hideImagingReportingValues = explode(',', $rights->imaging_reporting);
                    // if (in_array('1', $hideImagingReportingValues)) {
                    //     $hideImagingReporting = false;
                    // }
                    $hideMedicalCodingValues = explode(',', $rights->medical_coding);
                    if (in_array('1', $hideMedicalCodingValues)) {
                        $hideMedicalCoding = false;
                    }

                    $hideVitalSignValues = explode(',', $rights->vital_signs);
                    if (in_array('1', $hideVitalSignValues)) {
                        $hideVitalSign = false;
                    }

                    $hideEncounterProcedureValues = explode(',', $rights->encounters_and_procedures);
                    if (in_array('1', $hideEncounterProcedureValues)) {
                        $hideEncounterProcedure = false;
                    }

                    // $hideInvestigationTracking = explode(',', $rights->investigation_tracking);
                    // if (in_array('1', $hideInvestigationTrackingValues)) {
                    //     $hideInvestigationTracking = false;
                    // }

                    @endphp
                    @if (!$hideVitalSign || !$hideEncounterProcedure || !$hideMedicalCoding || !$hideInvestigationTracking)
                        <li> <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false"><i class="mdi mdi-medical-bag"></i><span class="hide-menu">Patient Medical Record</span></a>
                            <ul aria-expanded="false" class="collapse">
                                @if (!$hideMedicalCoding)
                                <li><a href="{{ route('icd-coding') }}">Medical Coding</a></li>
                                @endif

                                @if (!$hideVitalSign)
                                <li><a href="{{ route('vital-sign') }}">Vital Signs</a></li>
                                @endif

                                @if (!$hideEncounterProcedure)
                                <li><a href="{{ route('encounters-procedures') }}">Encounter & Procedures</a></li>
                                @endif

                                @if (!$hideInvestigationTracking)
                                <li><a href="#" class="sidebar-investigation-tracking">Investigation Order</a></li>
                                @endif

                                {{-- @if (!$hidePysicalAssesmet)
                                <li><a href="#">Physician Assessment</a></li>
                                @endif
                                @if (!$hideNursingAssessment)
                                    <li><a href="#">Nursing Assessment</a></li>
                                @endif
                                @if (!$hideCPOEMedication)
                                    <li><a href="#">CPOE For Medications</a></li>
                                @endif
                                @if (!$hideCPOEInvestigation)
                                    <li><a href="#">CPOE For Investigations</a></li>
                                @endif
                                @if (!$hideCPOEProcedure)
                                    <li><a href="#">CPOE For Procedures</a></li>
                                @endif
                                @if (!$hideCPOEServices)
                                    <li><a href="#">CPOE For Services</a></li>
                                @endif --}}
                                {{-- @if (!$hideLabSampleConfirm)
                                    <li><a href="#">Lab Sample Confirmation</a></li>
                                @endif
                                @if (!$hideImagingConfirm)
                                    <li><a href="#">Imaging Confirmation</a></li>
                                @endif
                                @if (!$hideLabReporting)
                                    <li><a href="#">Lab Reporting</a></li>
                                @endif
                                @if (!$hideImagingReporting)
                                    <li><a href="#">Imaging Reporting</a></li>
                                @endif --}}
                           
                            
                            </ul>
                        </li>
                    @endif

                    @php
                    $hideItemCat = true;
                    $hideItemSubCat = true;
                    $hideItemType = true;
                    $hideItemGenericSetup = true;
                    $hideItemBrand = true;
                    $hideTransactionType = true;
                    // $hideVendorRegistration = true;
                    $hidePurchaseOrder = true;
                    $hideWorkOrder = true;
                    $hideExternalTransaction = true;
                    $hideIssueDispense = true;
                    $hideConsumption = true;
                    $hideInventoryReturn = true;
                    $hideMaterialTransfer = true;
                    $hideReversalTransaction = true;
                    $hideMedicationRoutes = true;
                    $hideMedicationFrequency = true;
                    $hideRequisitionMaterialConsumption = true;
                    $hideRequisitionMaterialTransfer = true;
                    $hideThirdPartyRegistration = true;
                    $hideConsumptionGroup = true;
                    $hideConsumptionMethod = true;
                    $hideStockMonitoring = true;
                    $hideInventorySourceDestinationType = true;
                    $hideInventoryTransactionActivity = true;

                    $hideItemCatValues = explode(',', $rights->item_category);
                    if (in_array('1', $hideItemCatValues)) {
                        $hideItemCat = false;
                    }
                    $hideItemSubCatValues = explode(',', $rights->item_sub_category);
                    if (in_array('1', $hideItemSubCatValues)) {
                        $hideItemSubCat = false;
                    }
                    $hideItemTypeValues = explode(',', $rights->item_type);
                    if (in_array('1', $hideItemTypeValues)) {
                        $hideItemType = false;
                    }
                    $hideItemGenericSetupValues = explode(',', $rights->item_generic_setup);
                    if (in_array('1', $hideItemGenericSetupValues)) {
                        $hideItemGenericSetup = false;
                    }
                    $hideItemBrandValues = explode(',', $rights->item_brand_setup);
                    if (in_array('1', $hideItemBrandValues)) {
                        $hideItemBrand = false;
                    }
                    $hideTransactionTypeValues = explode(',', $rights->transaction_types);
                    if (in_array('1', $hideTransactionTypeValues)) {
                        $hideTransactionType = false;
                    }
                    // $hideVendorRegistrationValues = explode(',', $rights->vendor_registration);
                    // if (in_array('1', $hideVendorRegistrationValues)) {
                    //     $hideVendorRegistration = false;
                    // }
                    $hidePurchaseOrderValues = explode(',', $rights->purchase_order);
                    if (in_array('1', $hidePurchaseOrderValues)) {
                        $hidePurchaseOrder = false;
                    }
                    $hideWorkOrderValues = explode(',', $rights->work_order);
                    if (in_array('1', $hideWorkOrderValues)) {
                        $hideWorkOrder = false;
                    }
                    $hideExternalTransactionValues = explode(',', $rights->external_transaction);
                    if (in_array('1', $hideExternalTransactionValues)) {
                        $hideExternalTransaction = false;
                    }
                    $hideIssueDispenseValues = explode(',', $rights->issue_and_dispense);
                    if (in_array('1', $hideIssueDispenseValues)) {
                        $hideIssueDispense = false;
                    }
                    $hideConsumptionValues = explode(',', $rights->consumption);
                    if (in_array('1', $hideConsumptionValues)) {
                        $hideConsumption = false;
                    }
                    $hideInventoryReturnValues = explode(',', $rights->inventory_return);
                    if (in_array('1', $hideInventoryReturnValues)) {
                        $hideInventoryReturn = false;
                    }
                    $hideMaterialTransferValues = explode(',', $rights->material_transfer);
                    if (in_array('1', $hideMaterialTransferValues)) {
                        $hideMaterialTransfer = false;
                    }
                    $hideReversalTransactionValues = explode(',', $rights->reversal_of_transactions);
                    if (in_array('1', $hideReversalTransactionValues)) {
                        $hideReversalTransaction = false;
                    }
                    $hideMedicationRoutesValues = explode(',', $rights->medication_routes);
                    if (in_array('1', $hideMedicationRoutesValues)) {
                        $hideMedicationRoutes = false;
                    }
                    $hideMedicationFrequencyValues = explode(',', $rights->medication_frequency);
                    if (in_array('1', $hideMedicationFrequencyValues)) {
                        $hideMedicationFrequency = false;
                    }
                    $hideRequisitionMaterialConsumptionValues = explode(',', $rights->requisition_for_material_consumption);
                    if (in_array('1', $hideRequisitionMaterialConsumptionValues)) {
                        $hideRequisitionMaterialConsumption = false;
                    }
                    $hideRequisitionMaterialTransferValues = explode(',', $rights->requisition_for_material_transfer);
                    if (in_array('1', $hideRequisitionMaterialTransferValues)) {
                        $hideRequisitionMaterialTransfer = false;
                    }
                    $hideThirdPartyRegistrationValues = explode(',', $rights->third_party_registration);
                    if (in_array('1', $hideThirdPartyRegistrationValues)) {
                        $hideThirdPartyRegistration = false;
                    }
                    $hideConsumptionGroupValues = explode(',', $rights->consumption_group);
                    if (in_array('1', $hideConsumptionGroupValues)) {
                        $hideConsumptionGroup = false;
                    }
                    $hideConsumptionMethodValues = explode(',', $rights->consumption_method);
                    if (in_array('1', $hideConsumptionMethodValues)) {
                        $hideConsumptionMethod = false;
                    }
                    $hideStockMonitoringValues = explode(',', $rights->stock_monitoring);
                    if (in_array('1', $hideStockMonitoringValues)) {
                        $hideStockMonitoring = false;
                    }
                    $hideInventorySourceDestinationTypeValues = explode(',', $rights->inventory_source_destination_type);
                    if (in_array('1', $hideInventorySourceDestinationTypeValues)) {
                        $hideInventorySourceDestinationType = false;
                    }
                    $hideInventoryTransactionActivityValues = explode(',', $rights->inventory_transaction_activity);
                    if (in_array('1', $hideInventoryTransactionActivityValues)) {
                        $hideInventoryTransactionActivity = false;
                    }


                    @endphp
                    @if (!$hideItemCat || !$hideItemSubCat || !$hideItemType || !$hideItemGenericSetup || !$hideItemBrand || !$hideTransactionType || !$hidePurchaseOrder || !$hideWorkOrder || !$hideMedicationRoutes || !$hideMedicationFrequency || !$hideRequisitionMaterialConsumption || !$hideRequisitionMaterialTransfer)
                         <li> <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false"><i class="mdi mdi-database"></i><span class="hide-menu">Material Management</span></a>
                            <ul aria-expanded="false" class="collapse">
                                @if (!$hideItemCat || !$hideItemSubCat || !$hideItemType || !$hideItemGenericSetup || !$hideItemBrand)
                                <li>
                                    <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false">
                                        <span class="hide-menu">Item Setup</span>
                                    </a>
                                    <ul aria-expanded="false" class="collapse">
                                        @if (!$hideItemCat)
                                            <li><a href="{{ route('inventory-category') }}">Item Category</a></li>
                                        @endif
                                        @if (!$hideItemSubCat)
                                            <li><a href="{{ route('inventory-subcategory') }}">Item Sub-Category</a></li>
                                        @endif
                                        @if (!$hideItemType)
                                            <li><a href="{{ route('inventory-type') }}">Item Type</a></li>
                                        @endif
                                        @if (!$hideItemGenericSetup)
                                            <li><a href="{{ route('inventory-generic') }}">Item Generic Setup</a></li>
                                        @endif
                                        @if (!$hideItemBrand)
                                            <li><a href="{{ route('inventory-brand') }}">Item Brand Setup</a></li>
                                        @endif
                                    </ul>
                                </li>
                                @endif

                                @if (!$hideConsumptionGroup || !$hideConsumptionMethod || !$hideStockMonitoring)
                                <li>
                                    <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false">
                                        <span class="hide-menu">Inventory Consumption Setup</span>
                                    </a>
                                    <ul aria-expanded="false" class="collapse">
                                        @if (!$hideConsumptionGroup)
                                            <li><a href="{{ route('consumption-group') }}">Consumption Groups</a></li>
                                        @endif
                                    </ul>
                                    <ul aria-expanded="false" class="collapse">
                                        @if (!$hideConsumptionMethod)
                                            <li><a href="{{ route('consumption-method') }}">Consumption Methods</a></li>
                                        @endif
                                    </ul>

                                    <ul aria-expanded="false" class="collapse">
                                        @if (!$hideStockMonitoring)
                                            <li><a href="{{ route('stock-monitoring') }}">Stock Monitoring</a></li>
                                        @endif
                                    </ul>
                                </li>
                                @endif

                                @if (!$hideTransactionType  || $hideInventorySourceDestinationType || $hideInventoryTransactionActivity)
                                <li>
                                    <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false">
                                        <span class="hide-menu">Inventory Transaction Setup</span>
                                    </a>
                                    <ul aria-expanded="false" class="collapse">
                                        @if (!$hideInventorySourceDestinationType)
                                            <li><a href="{{ route('inventory-sourcedestination-type') }}">Source & Destination Type</a></li>
                                        @endif
                                    </ul>
                                    <ul aria-expanded="false" class="collapse">
                                        @if (!$hideInventoryTransactionActivity)
                                            <li><a href="{{ route('inventory-transaction-activity') }}">Transaction Activity</a></li>
                                        @endif
                                    </ul>
                                    <ul aria-expanded="false" class="collapse">
                                        @if (!$hideTransactionType)
                                            <li><a href="{{ route('inventory-transaction-type') }}">Transaction Types</a></li>
                                        @endif
                                    </ul>
                                 
                                  
                                </li>
                                @endif

                                @if (!$hideThirdPartyRegistration)
                                    <li><a href="{{ route('third-party-registration') }}">Third Party Registration</a></li>
                                @endif
                                {{-- @if (!$hideTransactionType)
                                    <li><a href="{{ route('inventory-transaction-type') }}">Transaction Types</a></li>
                                @endif --}}
                                {{-- @if (!$hideVendorRegistration)
                                    <li><a href="{{ route('vendor-registration') }}">Vendor Registration</a></li>
                                @endif --}}
                                @if (!$hidePurchaseOrder)
                                    <li><a href="{{ route('purchase-order') }}">Purchase Order</a></li>
                                @endif
                                @if (!$hideWorkOrder)
                                    <li><a href="{{ route('work-order') }}">Work Order</a></li>
                                @endif
                               
                                @if (!$hideExternalTransaction || !$hideIssueDispense || !$hideMaterialTransfer  || !$hideConsumption || !$hideInventoryReturn || !$hideReversalTransaction)
                                <li>
                                    <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false">
                                        <span class="hide-menu">Inventory Management</span>
                                    </a>
                                    <ul aria-expanded="false" class="collapse">
                                        @if (!$hideExternalTransaction)
                                            <li><a href="{{ route('external-transaction') }}">External Transactions</a></li>
                                        @endif
                                    </ul>
                                    <ul aria-expanded="false" class="collapse">
                                        @if (!$hideMaterialTransfer)
                                            <li><a href="{{ route('material-transfer') }}">Material Transfer</a></li>
                                        @endif
                                    </ul>
                                    <ul aria-expanded="false" class="collapse">
                                        @if (!$hideIssueDispense)
                                            <li><a href="{{ route('issue-dispense') }}">Issue & Dispense</a></li>
                                        @endif
                                    </ul>
                                  
                                    <ul aria-expanded="false" class="collapse">
                                        @if (!$hideConsumption)
                                            <li><a href="{{ route('consumption') }}">Consumption</a></li>
                                        @endif
                                    </ul>
                                    <ul aria-expanded="false" class="collapse">
                                        @if (!$hideInventoryReturn)
                                            <li> <a href="{{ route('inventory-return') }}">Return</a></li>
                                        @endif
                                    </ul>
                             
                                    <ul aria-expanded="false" class="collapse">
                                        @if (!$hideReversalTransaction)
                                            <li><a>Reversal Of Transactions</a></li>
                                        @endif
                                    </ul>
                                </li>
                                @endif
                                {{-- @if (!$hideInventoryManagement)
                                    <li><a href="{{ route('inventory-management') }}">Inventory Management</a></li>
                                @endif --}}
                                @if (!$hideMedicationRoutes)
                                    <li><a href="{{ route('medication-routes') }}">Medication Routes</a></li>
                                @endif
                                @if (!$hideMedicationFrequency)
                                    <li><a href="{{ route('medication-frequency') }}">Medication Frequency</a></li>
                                @endif
                                @if (!$hideRequisitionMaterialConsumption)
                                    <li><a href="{{ route('material-consumption') }}">Requisition For Material Consumption</a></li>
                                @endif
                                 @if (!$hideRequisitionMaterialTransfer)
                                    <li><a href="{{ route('req-material-transfer') }}">Requisition For Material Transfer</a></li>
                                @endif
                            </ul>
                        </li>
                    @endif

                    @php
                    $hideChartOfAccountStrategy = true;
                    $hideChartOfAccountStrategySetup = true;
                    $hideTransactionSourceDestination = true;
                    $hideFinancialLedgerType = true;
                    $hidePayrollAdditionSetup = true;
                    $hidePayrollDeductionSetup = true;
                    // $hideDonorRegistration = true;
                    $hideFinanceTransactionType = true;
                    $hideFinanceReceiving = true;
                    $hideFinancePayment = true;
                    $hideFinanceServiceRates = true;
                    $hideItemRates = true;
                    $hidePayroll = true;
                    $hideTaxation = true;
                    $hideLedger = true;

                    $hideChartOfAccountStrategyValues = explode(',', $rights->chart_of_accounts_strategy);
                    if (in_array('1', $hideChartOfAccountStrategyValues)) {
                        $hideChartOfAccountStrategy = false;
                    }
                    $hideChartOfAccountStrategySetupValues = explode(',', $rights->chart_of_accounts_strategy_setup);
                    if (in_array('1', $hideChartOfAccountStrategySetupValues)) {
                        $hideChartOfAccountStrategySetup = false;
                    }
                    $hideTransactionSourceDestinationValues = explode(',', $rights->transaction_sources_or_destinations);
                    if (in_array('1', $hideTransactionSourceDestinationValues)) {
                        $hideTransactionSourceDestination = false;
                    }
                    $hideFinancialLedgerTypeValues = explode(',', $rights->financial_ledger_types);
                    if (in_array('1', $hideFinancialLedgerTypeValues)) {
                        $hideFinancialLedgerType = false;
                    }
                    $hidePayrollAdditionSetupValues = explode(',', $rights->payroll_additions_setup);
                    if (in_array('1', $hidePayrollAdditionSetupValues)) {
                        $hidePayrollAdditionSetup = false;
                    }
                    $hidePayrollDeductionSetupValues = explode(',', $rights->payroll_deduction_setup);
                    if (in_array('1', $hidePayrollDeductionSetupValues)) {
                        $hidePayrollDeductionSetup = false;
                    }
                    // $hideDonorRegistrationValues = explode(',', $rights->donors_registration);
                    // if (in_array('1', $hideDonorRegistrationValues)) {
                    //     $hideDonorRegistration = false;
                    // }
                    $hideFinanceTransactionTypeValues = explode(',', $rights->finance_transaction_types);
                    if (in_array('1', $hideFinanceTransactionTypeValues)) {
                        $hideFinanceTransactionType = false;
                    }
                    $hideFinanceReceivingValues = explode(',', $rights->finance_receiving);
                    if (in_array('1', $hideFinanceReceivingValues)) {
                        $hideFinanceReceiving = false;
                    }
                    $hideFinancePaymentValues = explode(',', $rights->finance_payment);
                    if (in_array('1', $hideFinancePaymentValues)) {
                        $hideFinancePayment = false;
                    }
                    $hideFinanceServiceRateValues = explode(',', $rights->service_rates);
                    if (in_array('1', $hideFinanceServiceRateValues)) {
                        $hideFinanceServiceRates = false;
                    }
                    $hidePayrollValues = explode(',', $rights->payroll);
                    if (in_array('1', $hidePayrollValues)) {
                        $hidePayroll = false;
                    }
                    $hideTaxationValues = explode(',', $rights->taxation);
                    if (in_array('1', $hideTaxationValues)) {
                        $hideTaxation = false;
                    }
                    $hideLedgerValues = explode(',', $rights->ledger);
                    if (in_array('1', $hideLedgerValues)) {
                        $hideLedger = false;
                    }
                    $hideItemRatesValues = explode(',', $rights->item_rates);
                    if (in_array('1', $hideItemRatesValues)) {
                        $hideItemRates = false;
                    }
                    @endphp
                    @if (!$hideChartOfAccountStrategy || !$hideChartOfAccountStrategySetup || !$hideTransactionSourceDestination || !$hideFinancialLedgerType || !$hidePayrollAdditionSetup || !$hidePayrollDeductionSetup || !$hideFinanceTransactionType || !$hideFinanceReceiving || !$hideFinancePayment || !$hideItemRates || !$hidePayroll || !$hideTaxation || !$hideLedger)
                        <li> <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false"><i class="mdi mdi-bank"></i><span class="hide-menu">Finance</span></a>
                            <ul aria-expanded="false" class="collapse">
                                @if (!$hideChartOfAccountStrategy)
                                    <li><a href="{{ route('account-strategy') }}">Chart Of Accounts Strategy</a></li>
                                @endif
                                @if (!$hideChartOfAccountStrategySetup)
                                    <li><a href="{{ route('account-strategy-setup') }}">Chart Of Accounts Strategy Setup</a></li>
                                @endif
                                @if (!$hideTransactionSourceDestination)
                                    <li><a href="{{ route('transaction-source-destination') }}">Transaction Sources Or Destinations</a></li>
                                @endif
                                @if (!$hideFinancialLedgerType)
                                    <li><a href="{{ route('financial-ledger-types') }}">Financial Ledger Types</a></li>
                                @endif
                                @if (!$hidePayrollAdditionSetup)
                                    <li><a href="{{ route('financial-payroll-addition') }}">Payroll Additions Setup</a></li>
                                @endif
                                @if (!$hidePayrollDeductionSetup)
                                    <li><a href="{{ route('financial-payroll-deduction') }}">Payroll Deduction Setup</a></li>
                                @endif
                                {{-- @if (!$hideDonorRegistration)
                                    <li><a href="{{ route('donor-registration') }}">Donors Registration</a></li>
                                @endif --}}
                                @if (!$hideFinanceTransactionType)
                                    <li><a href="{{ route('finance-transaction-type') }}">Finance Transaction Types</a></li>
                                @endif
                                @if (!$hideFinanceReceiving || !$hideFinancePayment)
                                <li>
                                    <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false">
                                        <span class="hide-menu">Finance Payments</span>
                                    </a>
                                    <ul aria-expanded="false" class="collapse">
                                        @if (!$hideFinanceReceiving)
                                            <li><a href="{{ route('finance-receiving') }}">Receivings</a></li>
                                        @endif
                                        @if (!$hideFinancePayment)
                                            <li><a href="{{ route('finance-payment') }}">Payments</a></li>
                                        @endif
                                    </ul>
                                </li>
                                @endif

                                @if (!$hideFinanceServiceRates)
                                    <li><a href="{{ route('service-rates') }}">Service Rates</a></li>
                                @endif
                                @if (!$hideItemRates)
                                    <li><a href="{{ route('item-rates') }}">Item Rates</a></li>
                                @endif

                                @if (!$hidePayroll)
                                    <li><a href="#">PayRoll</a></li>
                                @endif
                                @if (!$hideTaxation)
                                    <li><a href="#">Taxation</a></li>
                                @endif
                                @if (!$hideLedger)
                                    <li><a href="#">Ledger</a></li>
                                @endif
                            </ul>
                        </li>
                    @endif

                    @php
                    $hideMSDComprehensiveReport = true; 
                    $hideInventoryReport = true; 
                    $hideMSDComprehensiveReportValues = explode(',', $rights->msd_comprehensive_report);
                    $hideInventoryReportValues = explode(',', $rights->inventory_report);
                    if (in_array('1', $hideMSDComprehensiveReportValues)) {
                        $hideMSDComprehensiveReport = false;
                    }
                    if (in_array('1', $hideInventoryReportValues)) {
                        $hideInventoryReport = false;
                    }
                    @endphp
                    @if (!$hideMSDComprehensiveReport || !$hideInventoryReport)
                        <li> <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false"><i class="mdi mdi-book-open-page-variant"></i><span class="hide-menu">Reports</span></a>
                            <ul aria-expanded="false" class="collapse">
                                @if (!$hideMSDComprehensiveReport)
                                    <li><a href="#">MSD Comprehensive Report</a></li>
                                @endif
                                @if (!$hideInventoryReport)
                                    <li><a href="inventory-report">Inventory Report</a></li>
                                @endif
                            </ul>
                        </li>
                    @endif

                    @php
                    $hideModules = true; 
                    $hideModulesValues = explode(',', $rights->modules);
                    if (in_array('1', $hideModulesValues)) {
                        $hideModules = false;
                    }
                    @endphp
                    @if (!$hideModules)
                        <li> <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false"><i class="mdi mdi-settings"></i><span class="hide-menu">Settings</span></a>
                            <ul aria-expanded="false" class="collapse">
                                <li><a href="{{ route('modules') }}">Modules</a></li>
                            </ul>
                        </li>
                    @endif

                         <!-- <li> <a class="waves-effect waves-dark" href="{{ route('form-setup') }}" aria-expanded="false"><i class="mdi mdi-clipboard-check"></i><span class="hide-menu">Forms Setup</span></a>  -->


                    </ul>
                </nav>
                <!-- End Sidebar navigation -->
            </div>
            <!-- End Sidebar scroll-->
        </aside>
        <!-- ============================================================== -->
        <!-- End Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->

        <!-- Custom styles for MR Selection Modal -->
        <style>
            #mr-selection-modal {
                z-index: 1050 !important;
                display: none;
            }
            #mr-selection-modal.show {
                display: block !important;
            }
            #mr-selection-modal .modal-dialog {
                z-index: 1051 !important;
                margin: 30px auto;
            }
            #mr-selection-modal .modal-content {
                z-index: 1052 !important;
                position: relative;
            }
            .modal-backdrop.show {
                z-index: 1040 !important;
            }
            /* Force modal to be visible when shown */
            #mr-selection-modal.modal.show {
                display: block !important;
                background-color: rgba(0, 0, 0, 0.5);
            }
        </style>

        <!-- MR Selection Modal for Investigation Tracking -->
        <div class="modal fade" id="mr-selection-modal" tabindex="-1" role="dialog" aria-labelledby="mrSelectionModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="mrSelectionModalLabel">Select Patient MR Number</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="sidebar-mr-select">Patient MR Number</label>
                            <select class="form-control selecter p-0" id="sidebar-mr-select" name="sidebar_mr_select" style="color:#222d32">
                                <option selected disabled>Select MR #</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="proceed-investigation-tracking">Proceed</button>
                    </div>
                </div>
            </div>
        </div>

        
