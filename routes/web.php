<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TerritoryController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\OrgSetupController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CostCenterController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\KeyPerformanceIndicatorController;
use App\Http\Controllers\SiteSetupController;
use App\Http\Controllers\HRController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\PatientMedicalRecord;
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::get('/clear', function () {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');

    return "All caches cleared!";
});

Route::post('/decrypt-data', function (Request $request) {
    try {
        return response()->json([
            'mr' => unserialize(Crypt::decryptString($request->input('mr'))),
            'billedamount' => unserialize(Crypt::decryptString($request->input('billedamount'))),
            'orgname' => unserialize(Crypt::decryptString($request->input('orgname'))),
            'orgid' => unserialize(Crypt::decryptString($request->input('orgid'))),
            'sitename' => unserialize(Crypt::decryptString($request->input('sitename'))),
            'siteid' => unserialize(Crypt::decryptString($request->input('siteid'))),
            'servicemode' => unserialize(Crypt::decryptString($request->input('servicemode'))),
            'servicemodeId' => unserialize(Crypt::decryptString($request->input('servicemodeId'))),
            'empname' => unserialize(Crypt::decryptString($request->input('empname'))),
            'empId' => unserialize(Crypt::decryptString($request->input('empId'))),
            'service' => unserialize(Crypt::decryptString($request->input('service'))),
            'serviceId' => unserialize(Crypt::decryptString($request->input('serviceId'))),
            'billingcc' => unserialize(Crypt::decryptString($request->input('billingcc'))),
            'billingccId' => unserialize(Crypt::decryptString($request->input('billingccId'))),
            'patientstatusval' => unserialize(Crypt::decryptString($request->input('patientstatusval'))),
            'patientstatus' => unserialize(Crypt::decryptString($request->input('patientstatus'))),
            'patientpriorityval' => unserialize(Crypt::decryptString($request->input('patientpriorityval'))),
            'patientpriority' => unserialize(Crypt::decryptString($request->input('patientpriority'))),
            'locationname' => unserialize(Crypt::decryptString($request->input('locationname'))),
            'locationid' => unserialize(Crypt::decryptString($request->input('locationid'))),
            'schedulename' => unserialize(Crypt::decryptString($request->input('schedulename'))),
            'scheduleid' => unserialize(Crypt::decryptString($request->input('scheduleid'))),
            'scheduleStartTime' => unserialize(Crypt::decryptString($request->input('scheduleStartTime'))),
            'scheduleEndTime' => unserialize(Crypt::decryptString($request->input('scheduleEndTime'))),
            'pattern' => unserialize(Crypt::decryptString($request->input('pattern'))),
            'remarks' => unserialize(Crypt::decryptString($request->input('remarks')))
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Decryption or deserialization failed'], 500);
    }
});

Route::get('/form-setup', function () {
    return view('dashboard.form');
})->name('form-setup');

Route::get('home', [App\Http\Controllers\UserController::class, 'Home'])->name('home');

//Roles
Route::get('user-roles', [App\Http\Controllers\UserController::class, 'viewRoles'])->name('user-roles');
Route::post('roles/addrole', [UserController::class, 'AddRole'])->name('roles.addrole');
Route::get('roles/data', [App\Http\Controllers\UserController::class, 'GetRolesData'])->name('roles.data');
Route::get('roles/update-status', [App\Http\Controllers\UserController::class, 'UpdateRoleStatus'])->name('roles.update-status');
Route::get('roles/{id}', [App\Http\Controllers\UserController::class, 'UpdateRoleModal'])->name('roles.UpdateRoleModal');
Route::post('update-role/{id}', [App\Http\Controllers\UserController::class, 'UpdateRole'])->name('roles.UpdateRole');
//Roles

// Rights
Route::get('rights-setup/{id}', [App\Http\Controllers\UserController::class, 'ViewRights'])->name('rights-setup');
Route::post('/rights-setup/assignrights', [UserController::class, 'AssignRights'])->name('rights-setup.assignrights');
Route::get('update-rights-setup/{id}', [App\Http\Controllers\UserController::class, 'UpdateRightsSetup'])->name('update-rights-setup');
Route::post('/rights/updaterights', [UserController::class, 'UpdateRights'])->name('UpdateRights');

// Rights

//Territory
Route::get('province', [App\Http\Controllers\TerritoryController::class, 'viewProvince'])->name('province');
Route::get('division', [App\Http\Controllers\TerritoryController::class, 'viewDivision'])->name('division');
Route::get('district', [App\Http\Controllers\TerritoryController::class, 'viewDistrict'])->name('district');
Route::post('territory/addprovince', [TerritoryController::class, 'AddProvince'])->name('territory.addprovince');
Route::post('territory/adddivision', [TerritoryController::class, 'AddDivision'])->name('territory.adddivision');
Route::post('territory/adddistrict', [TerritoryController::class, 'AddDistrict'])->name('territory.adddistrict');
Route::get('territory/getdivisions', [TerritoryController::class, 'GetSelectedDivisions'])->name('territory.getdivisions');
Route::get('territory/province', [App\Http\Controllers\TerritoryController::class, 'GetProvinceData'])->name('territory.province');
Route::get('territory/division', [App\Http\Controllers\TerritoryController::class, 'GetDivisionData'])->name('territory.division');
Route::get('territory/district', [App\Http\Controllers\TerritoryController::class, 'GetDistrictData'])->name('territory.district');
Route::get('territory/province/update-status', [App\Http\Controllers\TerritoryController::class, 'UpdateProvinceStatus'])->name('territory.province.update-status');
Route::get('territory/division/update-status', [App\Http\Controllers\TerritoryController::class, 'UpdateDivisionStatus'])->name('territory.division.update-status');
Route::get('territory/district/update-status', [App\Http\Controllers\TerritoryController::class, 'UpdateDistrictStatus'])->name('territory.district.update-status');
Route::get('territory/province/{id}', [App\Http\Controllers\TerritoryController::class, 'UpdateProvinceModal'])->name('UpdateProvinceModal');
Route::get('territory/division/{id}', [App\Http\Controllers\TerritoryController::class, 'UpdateDivisionModal'])->name('UpdateDivisionModal');
Route::get('territory/district/{id}', [App\Http\Controllers\TerritoryController::class, 'UpdateDistrictModal'])->name('UpdateDistrictModal');
Route::get('territory/updateprovince', [TerritoryController::class, 'UpdateSelectedProvince'])->name('territory.updateprovince');
Route::get('territory/updatedivision', [TerritoryController::class, 'UpdateSelectedDivisions'])->name('territory.updatedivision');
Route::get('territory/updatedistrict/', [App\Http\Controllers\TerritoryController::class, 'UpdateSelectedDistrict'])->name('UpdateSelectedDistrict');

Route::post('update-province/{id}', [App\Http\Controllers\TerritoryController::class, 'UpdateProvince'])->name('UpdateProvince');
Route::post('update-division/{id}', [App\Http\Controllers\TerritoryController::class, 'UpdateDivision'])->name('UpdateDivision');
Route::post('update-district/{id}', [App\Http\Controllers\TerritoryController::class, 'UpdateDistrict'])->name('UpdateDistrict');
//Territory

//Logs
Route::get('viewlogs/{id}', [App\Http\Controllers\LogController::class, 'ViewLogs'])->name('viewlogs');
//Logs

//Organization
Route::post('orgSetup/addorganization', [OrgSetupController::class, 'AddOrganization'])->name('orgSetup.addorganization');
Route::get('orgsetup', [App\Http\Controllers\OrgSetupController::class, 'viewOrganization'])->name('orgsetup');
Route::get('orgSetup/getdistrict', [OrgSetupController::class, 'GetSelectedDistrict'])->name('orgSetup.getdistrict');
Route::get('orgSetup/vieworganization', [App\Http\Controllers\OrgSetupController::class, 'GetOrganizationData'])->name('orgSetup.vieworganization');
Route::get('orgSetup/update-status', [App\Http\Controllers\OrgSetupController::class, 'UpdateOrganizationStatus'])->name('orgSetup.update-status');
Route::get('orgSetup/detail/{id}', [App\Http\Controllers\OrgSetupController::class, 'OrganizationDetailModal'])->name('OrganizationDetailModal');
Route::get('orgSetup/updatemodal/{id}', [App\Http\Controllers\OrgSetupController::class, 'UpdateOrganizationModal'])->name('UpdateOrganizationModal');
Route::post('edit-org/{id}', [App\Http\Controllers\OrgSetupController::class, 'UpdateOrganization'])->name('UpdateOrganization');
Route::get('orgSetup/GetOrganization', [OrgSetupController::class, 'GetSelectedOrganization'])->name('orgSetup/GetOrganization');
Route::get('orgSetup/GetTransactionTypeOrganization', [OrgSetupController::class, 'GetSelectedTransactionTypeOrganization'])->name('orgSetup/GetTransactionTypeOrganization');
//Organization

// Referral Site Setup
Route::get('referral-setup', [App\Http\Controllers\OrgSetupController::class, 'ShowReferralSite'])->name('referral-setup');
Route::post('orgSetup/addreferralsite', [OrgSetupController::class, 'AddReferralSite'])->name('AddReferralSite');
Route::get('orgSetup/viewreferralsite', [App\Http\Controllers\OrgSetupController::class, 'ShowReferralSiteDate'])->name('ShowReferralSiteDate');
Route::get('orgSetup/rs-status', [App\Http\Controllers\OrgSetupController::class, 'UpdateReferralSiteStatus'])->name('UpdateReferralSiteStatus');
Route::get('orgSetup/updatereferralsite/{id}', [OrgSetupController::class, 'UpdateReferralSiteModal']);
Route::post('orgSetup/updatereferralsite/{id}', [OrgSetupController::class, 'UpdateReferralSite']);
Route::get('orgSetup/getreferralsites', [OrgSetupController::class, 'GetSelectedReferralSites'])->name('orgSetup/getreferralsites');


// Route::get('inventory/updateconsumptiongroup/{id}', [App\Http\Controllers\InventoryController::class, 'UpdateConsumptionGroupModal'])->name('UpdateConsumptionGroupModal');
// Route::post('inventory/update-consumptiongroup/{id}', [App\Http\Controllers\InventoryController::class, 'UpdateConsumptionGroup'])->name('UpdateConsumptionGroup');

// Referral Site Setup

// User
Route::get('user', [App\Http\Controllers\UserController::class, 'viewUser'])->name('user');
Route::post('user/adduser', [UserController::class, 'AddUser'])->name('user.adduser');
Route::get('user/data', [App\Http\Controllers\UserController::class, 'GetUserData'])->name('user.data');
Route::get('user/update-status', [App\Http\Controllers\UserController::class, 'UpdateUserStatus'])->name('user.update-status');
Route::get('user/editdata/{id}', [App\Http\Controllers\UserController::class, 'UpdateUserModal'])->name('UpdateUserModal');
Route::get('user/updaterole', [UserController::class, 'UpdateSelectedRole'])->name('UpdateSelectedRole');
Route::post('update-user/{id}', [App\Http\Controllers\UserController::class, 'UpdateUser'])->name('UpdateUser');
Route::get('profile', [App\Http\Controllers\UserController::class, 'MyProfile'])->name('profile');
Route::post('userImg/{id}', [App\Http\Controllers\UserController::class, 'UpdateProfile'])->name('UpdateProfile');
// User

//Auth
Route::get('/', [App\Http\Controllers\AuthController::class, 'viewLogin'])->name('viewLogin');
Route::post('authenticate', [App\Http\Controllers\AuthController::class, 'Auth'])->name('Auth');
Route::get('dashboard', [App\Http\Controllers\AuthController::class, 'AuthAdmin'])->name('dashboard');
Route::get('organization', [App\Http\Controllers\AuthController::class, 'AuthOrg'])->name('organization');
Route::get('logout', [AuthController::class, 'logout'])->name('logout');
Route::post('updatepwd', [App\Http\Controllers\AuthController::class, 'UpdatePwd'])->name('updatepwd');
Route::post('forgetPwd', [App\Http\Controllers\AuthController::class, 'ForgetPassword'])->name('ForgetPassword');
Route::get('resetPwd/{token}', [App\Http\Controllers\AuthController::class, 'ResetPassword'])->name('ResetPassword');
//Auth

// Cost Center
Route::get('cost-center', [App\Http\Controllers\CostCenterController::class, 'CostCenter'])->name('cost-center');
Route::get('cost-center-type', [App\Http\Controllers\CostCenterController::class, 'CostCenterType'])->name('cost-center-type');
Route::post('costcenter/addCCType', [CostCenterController::class, 'AddCostCenterType'])->name('costcenter.addCCType');
Route::get('costcenter/cctype', [App\Http\Controllers\CostCenterController::class, 'GetCostCenterTypeData'])->name('costcenter.cctype');
Route::get('costcentertype/update-status', [App\Http\Controllers\CostCenterController::class, 'UpdateCCTypeStatus'])->name('costcentertype.update-status');
Route::get('costcenter/updateCCtype/{id}', [App\Http\Controllers\CostCenterController::class, 'UpdateCCTypeModal'])->name('UpdateCCTypeModal');
Route::post('update-cctype/{id}', [App\Http\Controllers\CostCenterController::class, 'UpdateCCType'])->name('UpdateCCType');

Route::post('costcenter/addcostcenter', [CostCenterController::class, 'AddCostCenter'])->name('costcenter.addcostcenter');
Route::get('costcenter/ccdata', [App\Http\Controllers\CostCenterController::class, 'GetCostCenterData'])->name('costcenter.ccdata');
Route::get('costcenter/update-status', [App\Http\Controllers\CostCenterController::class, 'UpdateCostCenterStatus'])->name('costcenter.update-status');
Route::get('costcenter/updatecostcenter/{id}', [App\Http\Controllers\CostCenterController::class, 'UpdateCostCenterModal'])->name('UpdateCostCenterModal');
Route::get('costcenter/getcctype', [CostCenterController::class, 'GetSelectedCCType'])->name('costcenter.getcctype');
Route::post('update-costcenter/{id}', [App\Http\Controllers\CostCenterController::class, 'UpdateCostCenter'])->name('UpdateCostCenter');

Route::get('cc-activation', [App\Http\Controllers\CostCenterController::class, 'CostCenterActivation'])->name('cc-activation');
Route::get('costcenter/getactivatedcc', [CostCenterController::class, 'GetSelectedCC'])->name('GetSelectedCC');
Route::get('costcenter/getorderingperformingcc', [CostCenterController::class, 'GetOrderingPerformingCC'])->name('GetOrderingPerformingCC');
Route::get('costcenter/getorderingcc', [CostCenterController::class, 'GetOrderingCC'])->name('GetOrderingCC');
Route::get('costcenter/getperformingcc', [CostCenterController::class, 'GetPerformingCC'])->name('GetPerformingCC');
Route::get('costcenter/getnotactivatedcc', [CostCenterController::class, 'GetNotActivatedCC'])->name('costcenter/getnotactivatedcc');
Route::get('costcenter/getallactivatedcc', [CostCenterController::class, 'GetAllActivatedCC'])->name('costcenter/getallactivatedcc');


Route::post('costcenter/activatecc', [CostCenterController::class, 'ActivateCostCenter'])->name('ActivateCostCenter');
Route::get('costcenter/getactivateccdata', [App\Http\Controllers\CostCenterController::class, 'GetActivatedCCData'])->name('costcenter.getactivateccdata');
Route::get('costcenter/update-activatecc', [App\Http\Controllers\CostCenterController::class, 'UpdateActivatedCCStatus'])->name('costcenter.update-activatecc');
Route::get('costcenter/updateactivatecc/{id}', [App\Http\Controllers\CostCenterController::class, 'UpdateActivatedCCModal'])->name('updateactivatecc');
Route::get('costcenter/getselectedcc', [CostCenterController::class, 'GetSelectedCostCenter'])->name('costcenter.getselectedcc');
Route::post('update-activatecc/{id}', [App\Http\Controllers\CostCenterController::class, 'UpdateActivatedCC'])->name('update-activatecc');

// Cost Center

//Service
Route::get('service-mode', [App\Http\Controllers\ServicesController::class, 'ServiceMode'])->name('service-mode');
Route::post('services/addservicemode', [ServicesController::class, 'AddServiceMode'])->name('services.addservicemode');
Route::get('services/servicemode', [App\Http\Controllers\ServicesController::class, 'GetServiceModeData'])->name('services.servicemode');
Route::get('services/sm-status', [App\Http\Controllers\ServicesController::class, 'UpdateServiceModeStatus'])->name('services.sm-status');
Route::get('services/updateservicemode/{id}', [App\Http\Controllers\ServicesController::class, 'UpdateServiceModeModal'])->name('UpdateServiceModeModal');
Route::post('services/update-servicemode/{id}', [App\Http\Controllers\ServicesController::class, 'UpdateServiceMode'])->name('UpdateServiceMode');

Route::get('service-type', [App\Http\Controllers\ServicesController::class, 'ServiceType'])->name('service-type');
Route::post('services/addservicetype', [ServicesController::class, 'AddServiceType'])->name('services.addservicetype');
Route::get('services/servicetype', [App\Http\Controllers\ServicesController::class, 'GetServiceTypeData'])->name('services.servicetype');
Route::get('services/st-status', [App\Http\Controllers\ServicesController::class, 'UpdateServiceTypeStatus'])->name('services.st-status');
Route::get('services/updateservicetype/{id}', [App\Http\Controllers\ServicesController::class, 'UpdateServiceTypeModal'])->name('UpdateServiceTypeModal');
Route::post('services/update-servicetype/{id}', [App\Http\Controllers\ServicesController::class, 'UpdateServiceType'])->name('UpdateServiceType');

Route::get('service-unit', [App\Http\Controllers\ServicesController::class, 'ServiceUnit'])->name('service-unit');
Route::post('services/addserviceunit', [ServicesController::class, 'AddServiceUnit'])->name('AddServiceUnit');
Route::get('services/serviceunit', [App\Http\Controllers\ServicesController::class, 'GetServiceUnitData'])->name('GetServiceUnitData');
Route::get('services/su-status', [App\Http\Controllers\ServicesController::class, 'UpdateServiceUnitStatus'])->name('UpdateServiceUnitStatus');
Route::get('services/updateserviceunit/{id}', [App\Http\Controllers\ServicesController::class, 'UpdateServiceUnitModal'])->name('UpdateServiceUnitModal');
Route::post('services/update-serviceunit/{id}', [App\Http\Controllers\ServicesController::class, 'UpdateServiceUnit'])->name('UpdateServiceUnit');

Route::get('service-group', [App\Http\Controllers\ServicesController::class, 'ServiceGroup'])->name('service-group');
Route::post('services/addservicegroup', [ServicesController::class, 'AddServiceGroup'])->name('services.addservicegroup');
Route::get('services/servicegroup', [App\Http\Controllers\ServicesController::class, 'GetServiceGroupData'])->name('services.servicegroup');
Route::get('services/sg-status', [App\Http\Controllers\ServicesController::class, 'UpdateServiceGroupStatus'])->name('services.sg-status');
Route::get('services/updateservicegroup/{id}', [App\Http\Controllers\ServicesController::class, 'UpdateServiceGroupModal'])->name('UpdateServiceGroupModal');
Route::get('services/getservicetype', [ServicesController::class, 'GetSelectedServiceType'])->name('services.getservicetype');
Route::post('services/update-servicegroup/{id}', [App\Http\Controllers\ServicesController::class, 'UpdateServiceGroup'])->name('UpdateServiceGroup');


Route::get('services', [App\Http\Controllers\ServicesController::class, 'ShowServices'])->name('services');
Route::post('services/addservices', [ServicesController::class, 'AddServices'])->name('AddServices');
Route::get('services/getservices', [App\Http\Controllers\ServicesController::class, 'GetServiceData'])->name('GetServiceData');
Route::get('services/service-status', [App\Http\Controllers\ServicesController::class, 'UpdateServiceStatus'])->name('UpdateServiceStatus');
Route::get('services/updateservices/{id}', [App\Http\Controllers\ServicesController::class, 'UpdateServiceModal'])->name('UpdateServiceModal');
Route::get('services/getservicegroup', [ServicesController::class, 'GetSelectedServiceGroups'])->name('GetSelectedServiceGroups');
Route::post('services/update-services/{id}', [App\Http\Controllers\ServicesController::class, 'UpdateServices'])->name('UpdateServices');

Route::get('service-activation', [App\Http\Controllers\ServicesController::class, 'ServiceActivation'])->name('service-activation');
Route::post('services/activateservice', [ServicesController::class, 'ActivateService'])->name('ActivateService');
Route::get('services/getactivateservicedata', [App\Http\Controllers\ServicesController::class, 'GetActivatedServiceData'])->name('GetActivatedServiceData');
Route::get('services/update-activateservice', [App\Http\Controllers\ServicesController::class, 'UpdateActivatedServiceStatus'])->name('UpdateActivatedServiceStatus');
Route::get('services/updateactivateservice/{id}', [App\Http\Controllers\ServicesController::class, 'UpdateActivatedServiceModal'])->name('UpdateActivatedServiceModal');
Route::get('services/getselectedservices', [ServicesController::class, 'GetSelectedServices'])->name('GetSelectedServices');
Route::post('update-activateservice/{id}', [App\Http\Controllers\ServicesController::class, 'UpdateActivatedService'])->name('UpdateActivatedService');
Route::get('services/getactivatedservices', [ServicesController::class, 'GetServices'])->name('GetServices');
Route::get('services/getactivatedepiservices', [ServicesController::class, 'GetEPIServices'])->name('GetEPIServices');
Route::get('services/allocateempservices', [ServicesController::class, 'AllocateServiceToEmp'])->name('AllocateServiceToEmp');
Route::get('services/getactivatedservicemodes', [ServicesController::class, 'GetServiceModes'])->name('GetServiceModes');
Route::get('services/getservicecostcenter', [ServicesController::class, 'GetServiceCostCenters'])->name('GetServiceCostCenters');
Route::get('services/getmrservices', [App\Http\Controllers\ServicesController::class, 'GetMRServiceData'])->name('GetMRServiceData');
Route::post('services/getservicedetails', [App\Http\Controllers\ServicesController::class, 'GetServiceDetailsIssueDispense'])->name('GetServiceDetailsIssueDispense');

Route::get('service-location', [App\Http\Controllers\ServicesController::class, 'ShowServiceLocation'])->name('service-location');
Route::post('services/addservicelocation', [ServicesController::class, 'AddServiceLocation'])->name('AddServiceLocation');
Route::get('services/viewservicelocation', [App\Http\Controllers\ServicesController::class, 'ViewServiceLocation'])->name('ViewServiceLocation');
Route::get('services/servicelocation-status', [App\Http\Controllers\ServicesController::class, 'UpdateServiceLocationStatus'])->name('UpdateServiceLocationStatus');
Route::get('services/servicelocationmodal/{id}', [App\Http\Controllers\ServicesController::class, 'UpdateServiceLocationModal'])->name('UpdateServiceLocationModal');
Route::post('services/update-servicelocation/{id}', [App\Http\Controllers\ServicesController::class, 'UpdateServiceLocation'])->name('UpdateServiceLocation');

Route::get('service-location-activation', [App\Http\Controllers\ServicesController::class, 'ServiceLocationActivation'])->name('service-location-activation');
Route::get('services/getnotactivatedsl', [ServicesController::class, 'GetNotActivatedServiceLocation'])->name('services/getnotactivatedsl');
Route::post('services/activatesl', [ServicesController::class, 'ActivateServiceLocation'])->name('ActivateServiceLocation');
Route::get('services/getactivatesldata', [App\Http\Controllers\ServicesController::class, 'GetActivatedSLData'])->name('GetActivatedSLData');
Route::get('services/activatesl-status', [App\Http\Controllers\ServicesController::class, 'UpdateActivatedServiceLocationStatus'])->name('UpdateActivatedServiceLocationStatus');
Route::get('services/activateslmodal/{id}', [App\Http\Controllers\ServicesController::class, 'UpdateActivatedServiceLocationModal'])->name('UpdateActivatedServiceLocationModal');
Route::post('services/update-activatesl/{id}', [App\Http\Controllers\ServicesController::class, 'UpdateActivatedServiceLocation'])->name('UpdateActivatedServiceLocation');
Route::get('services/getactivatedsl', [ServicesController::class, 'GetActivatedServiceLocation'])->name('services/getactivatedsl');
Route::get('services/getallsl', [ServicesController::class, 'GetAllServiceLocations'])->name('services/getallsl');


Route::get('services/getservicelocation', [App\Http\Controllers\ServicesController::class, 'GetServiceLocation'])->name('GetServiceLocation');
Route::get('service-location-scheduling', [App\Http\Controllers\ServicesController::class, 'ShowServiceLocationScheduling'])->name('service-location-scheduling');
Route::post('services/addlocationscheduling', [ServicesController::class, 'AddServiceLocationScheduling'])->name('AddServiceLocationScheduling');
Route::get('services/viewlocationscheduling', [App\Http\Controllers\ServicesController::class, 'ViewServiceLocationScheduling'])->name('ViewServiceLocationScheduling');
Route::get('services/locationscheduling-status', [App\Http\Controllers\ServicesController::class, 'UpdateServiceLocationSchedulingStatus'])->name('UpdateServiceLocationSchedulingStatus');
Route::get('services/locationschedulingmodal/{id}', [App\Http\Controllers\ServicesController::class, 'UpdateServiceLocationSchedulingModal'])->name('UpdateServiceLocationSchedulingModal');
Route::post('services/update-locationschedule/{id}', [App\Http\Controllers\ServicesController::class, 'UpdateServiceLocationSchedule'])->name('UpdateServiceLocationSchedule');

Route::get('service-booking', [App\Http\Controllers\ServicesController::class, 'ShowServiceBooking'])->name('service-booking');
Route::post('services/addservicebooking', [ServicesController::class, 'AddServiceBooking'])->name('AddServiceBooking');
Route::get('services/viewservicebooking', [App\Http\Controllers\ServicesController::class, 'ViewServiceBooking'])->name('ViewServiceBooking');
Route::get('services/servicebooking-status', [App\Http\Controllers\ServicesController::class, 'UpdateServiceBookingStatus'])->name('UpdateServiceBookingStatus');
Route::get('services/servicebookingmodal/{id}', [App\Http\Controllers\ServicesController::class, 'UpdateServiceBookingModal'])->name('UpdateServiceBookingModal');
Route::get('services/getserviceschedule', [App\Http\Controllers\ServicesController::class, 'GetServiceSchedule'])->name('GetServiceSchedule');
Route::post('services/update-servicebooking/{id}', [App\Http\Controllers\ServicesController::class, 'UpdateServiceBooking'])->name('UpdateServiceBooking');
Route::get('patient/MRcode', [App\Http\Controllers\PatientController::class, 'PatientMRNo'])->name('PatientMRNo');
Route::get('patient/orgPatient', [App\Http\Controllers\PatientController::class, 'OrganizationPatient'])->name('OrganizationPatient');

Route::get('service-requisition-setup', [App\Http\Controllers\ServicesController::class, 'ShowServiceRequsitionSetup'])->name('service-requisition-setup');
// Route::post('services/addservicerequisition', [ServicesController::class, 'AddServiceRequisition'])->name('AddServiceRequisition');
Route::get('services/viewservicerequisition', [App\Http\Controllers\ServicesController::class, 'ViewServiceRequisition'])->name('ViewServiceRequisition');
Route::get('services/servicerequisition-status', [App\Http\Controllers\ServicesController::class, 'UpdateServiceRequisitionStatus'])->name('UpdateServiceRequisitionStatus');
Route::get('services/servicerequisitionmodal/{id}', [App\Http\Controllers\ServicesController::class, 'UpdateServiceRequisitionModal'])->name('UpdateServiceRequisitionModal');
Route::post('services/update-servicerequisition/{id}', [App\Http\Controllers\ServicesController::class, 'UpdateServiceRequisition'])->name('UpdateServiceRequisition');



//Service

// Key Performance Indicators
Route::get('kpi-group', [App\Http\Controllers\KeyPerformanceIndicatorController::class, 'KPIGroup'])->name('kpi-group');
Route::post('kpi/addkpigroup', [KeyPerformanceIndicatorController::class, 'AddKPIGroup'])->name('kpi.addkpigroup');
Route::get('kpi/kpigroup', [App\Http\Controllers\KeyPerformanceIndicatorController::class, 'GetKPIGroupData'])->name('kpi.kpigroup');
Route::get('kpi/kg-status', [App\Http\Controllers\KeyPerformanceIndicatorController::class, 'UpdateKPIGroupStatus'])->name('kpi.kg-status');
Route::get('kpi/updatekpigroup/{id}', [App\Http\Controllers\KeyPerformanceIndicatorController::class, 'UpdateKPIGroupModal'])->name('UpdateKPIGroupModal');
Route::post('kpi/update-kpigroup/{id}', [App\Http\Controllers\KeyPerformanceIndicatorController::class, 'UpdateKPIGroup'])->name('UpdateKPIGroup');

Route::get('kpi-dimension', [App\Http\Controllers\KeyPerformanceIndicatorController::class, 'KPIDimension'])->name('kpi-dimension');
Route::post('kpi/addkpidimension', [KeyPerformanceIndicatorController::class, 'AddKPIDimension'])->name('kpi.addkpidimension');
Route::get('kpi/kpidimension', [App\Http\Controllers\KeyPerformanceIndicatorController::class, 'GetKPIDimensionData'])->name('kpi.kpidimension');
Route::get('kpi/kd-status', [App\Http\Controllers\KeyPerformanceIndicatorController::class, 'UpdateKPIDimensionStatus'])->name('kpi.kd-status');
Route::get('kpi/updatekpidimension/{id}', [App\Http\Controllers\KeyPerformanceIndicatorController::class, 'UpdateKPIDimensionModal'])->name('UpdateKPIDimensionModal');
Route::post('kpi/update-kpidimension/{id}', [App\Http\Controllers\KeyPerformanceIndicatorController::class, 'UpdateKPIDimension'])->name('UpdateKPIDimension');


Route::get('kpi-type', [App\Http\Controllers\KeyPerformanceIndicatorController::class, 'KPIType'])->name('kpi-type');
Route::post('kpi/addkpitype', [KeyPerformanceIndicatorController::class, 'AddKPIType'])->name('kpi.addkpitype');
Route::get('kpi/kpitype', [App\Http\Controllers\KeyPerformanceIndicatorController::class, 'GetKPITypeData'])->name('kpi.kpitype');
Route::get('kpi/kt-status', [App\Http\Controllers\KeyPerformanceIndicatorController::class, 'UpdateKPITypeStatus'])->name('kpi.kt-status');
Route::get('kpi/updatekpitype/{id}', [App\Http\Controllers\KeyPerformanceIndicatorController::class, 'UpdateKPITypeModal'])->name('UpdateKPITypeModal');
Route::get('kpi/getkpigroup', [KeyPerformanceIndicatorController::class, 'GetKPIGroup'])->name('kpi.getkpigroup');
Route::get('kpi/getkpidimension', [KeyPerformanceIndicatorController::class, 'GetKPIdimension'])->name('kpi.getkpidimension');
Route::post('kpi/update-kpitype/{id}', [App\Http\Controllers\KeyPerformanceIndicatorController::class, 'UpdateKPIType'])->name('UpdateKPIType');


Route::get('kpi', [App\Http\Controllers\KeyPerformanceIndicatorController::class, 'ShowKPI'])->name('kpi');
Route::post('kpi/addkpi', [KeyPerformanceIndicatorController::class, 'AddKPI'])->name('AddKPI');
Route::get('kpi/kpi', [App\Http\Controllers\KeyPerformanceIndicatorController::class, 'GetKPIData'])->name('GetKPIData');
Route::get('kpi/kpi-status', [App\Http\Controllers\KeyPerformanceIndicatorController::class, 'UpdateKPIStatus'])->name('UpdateKPIStatus');
Route::get('kpi/updatekpi/{id}', [App\Http\Controllers\KeyPerformanceIndicatorController::class, 'UpdateKPIModal'])->name('UpdateKPIModal');
Route::get('kpi/getkpitype', [KeyPerformanceIndicatorController::class, 'GetKPITypes'])->name('GetKPITypes');
Route::post('kpi/update-kpi/{id}', [App\Http\Controllers\KeyPerformanceIndicatorController::class, 'UpdateKPI'])->name('UpdateKPI');

Route::get('kpi-activation', [App\Http\Controllers\KeyPerformanceIndicatorController::class, 'KPIActivation'])->name('kpi-activation');
Route::post('kpi/activatekpi', [KeyPerformanceIndicatorController::class, 'ActivateKPI'])->name('ActivateKPI');
Route::get('kpi/getactivatekpidata', [App\Http\Controllers\KeyPerformanceIndicatorController::class, 'GetActivatedKPIData'])->name('GetActivatedKPIData');
Route::get('kpi/update-activatekpi', [App\Http\Controllers\KeyPerformanceIndicatorController::class, 'UpdateActivatedKPIStatus'])->name('UpdateActivatedKPIStatus');
Route::get('kpi/updateactivatekpi/{id}', [App\Http\Controllers\KeyPerformanceIndicatorController::class, 'UpdateActivatedKPIModal'])->name('UpdateActivatedKPIModal');
Route::get('kpi/getselectedkpi', [KeyPerformanceIndicatorController::class, 'GetSelectedKPI'])->name('GetSelectedKPI');
Route::post('update-activatekpi/{id}', [App\Http\Controllers\KeyPerformanceIndicatorController::class, 'UpdateActivatedKPI'])->name('UpdateActivatedKPI');

// Key Performance Indicators

// Site Setup
Route::get('site-setup', [App\Http\Controllers\SiteSetupController::class, 'SiteSetup'])->name('site-setup');
Route::post('site/addsite', [SiteSetupController::class, 'AddSite'])->name('site.addsite');
Route::get('site/sitedata', [App\Http\Controllers\SiteSetupController::class, 'GetSiteData'])->name('site.sitedata');
Route::get('site/detail/{id}', [App\Http\Controllers\SiteSetupController::class, 'SiteDetailModal'])->name('SiteDetailModal');
Route::get('site/site-status', [App\Http\Controllers\SiteSetupController::class, 'UpdateSiteStatus'])->name('site.site-status');
Route::get('site/updatesite/{id}', [App\Http\Controllers\SiteSetupController::class, 'UpdateSiteModal'])->name('UpdateSiteModal');
Route::post('site/update-site/{id}', [App\Http\Controllers\SiteSetupController::class, 'UpdateSiteData'])->name('UpdateSiteData');
Route::get('site/getselectedsite', [SiteSetupController::class, 'GetSelectedSite'])->name('GetSelectedSite');
Route::get('site/getsites', [SiteSetupController::class, 'GetSelectedSites'])->name('site/getsites');
// Site Setup



//HR
Route::get('employee-gender', [App\Http\Controllers\HRController::class, 'EmployeeGender'])->name('employee-gender');
Route::post('hr/addgender', [App\Http\Controllers\HRController::class, 'AddGender'])->name('hr.addgender');
Route::get('hr/genderdata', [App\Http\Controllers\HRController::class, 'GetEmployeeGenderData'])->name('hr.genderdata');
Route::get('hr/gender-status', [App\Http\Controllers\HRController::class, 'UpdateEmployeeGenderStatus'])->name('UpdateEmployeeGenderStatus');
Route::get('hr/updategender/{id}', [App\Http\Controllers\HRController::class, 'UpdateGenderModal'])->name('UpdateGenderModal');
Route::post('hr/update-gender/{id}', [App\Http\Controllers\HRController::class, 'UpdateGender'])->name('UpdateGender');

Route::get('prefix-setup', [App\Http\Controllers\HRController::class, 'PrefixSetup'])->name('prefix-setup');
Route::post('hr/add_prefix', [App\Http\Controllers\HRController::class, 'AddPrefix'])->name('AddPrefix');
Route::get('hr/prefixdata', [App\Http\Controllers\HRController::class, 'GetPrefixData'])->name('GetPrefixData');
Route::get('hr/prefix-status', [App\Http\Controllers\HRController::class, 'UpdatePrefixStatus'])->name('UpdatePrefixStatus');
Route::get('hr/updateprefix/{id}', [App\Http\Controllers\HRController::class, 'UpdatePrefixModal'])->name('UpdatePrefixModal');
Route::post('hr/update-prefix/{id}', [App\Http\Controllers\HRController::class, 'UpdatePrefix'])->name('UpdatePrefix');

Route::get('employee-status', [App\Http\Controllers\HRController::class, 'EmployeeStatus'])->name('employee-status');
Route::post('hr/addempStatus', [App\Http\Controllers\HRController::class, 'AddEmployeeStatus'])->name('hr.addempStatus');
Route::get('hr/empStatusdata', [App\Http\Controllers\HRController::class, 'GetEmployeeStatusData'])->name('hr.empStatusdata');
Route::get('hr/emp-status', [App\Http\Controllers\HRController::class, 'UpdateEmployeeStatus'])->name('UpdateEmployeeStatus');
Route::get('hr/updateempStatus/{id}', [App\Http\Controllers\HRController::class, 'UpdateEmployeeStatusModal'])->name('UpdateEmployeeStatusModal');
Route::post('hr/update-empStatus/{id}', [App\Http\Controllers\HRController::class, 'UpdateEmployeeStatusData'])->name('UpdateEmployeeStatusData');

Route::get('working-status', [App\Http\Controllers\HRController::class, 'EmployeeWorkingStatus'])->name('working-status');
Route::post('hr/addworkingStatus', [App\Http\Controllers\HRController::class, 'AddEmployeeWorkingStatus'])->name('AddEmployeeWorkingStatus');
Route::get('hr/workingStatusdata', [App\Http\Controllers\HRController::class, 'GetEmployeeWorkingStatusData'])->name('GetEmployeeWorkingStatusData');
Route::get('hr/working-status', [App\Http\Controllers\HRController::class, 'UpdateEmployeeWorkingStatus'])->name('UpdateEmployeeWorkingStatus');
Route::get('hr/updateworkingStatus/{id}', [App\Http\Controllers\HRController::class, 'UpdateEmployeeWorkingStatusModal'])->name('UpdateEmployeeWorkingStatusModal');
Route::post('hr/update-workingStatus/{id}', [App\Http\Controllers\HRController::class, 'UpdateEmployeeWorkingStatusData'])->name('UpdateEmployeeWorkingStatusData');

Route::get('emp-qualification-level', [App\Http\Controllers\HRController::class, 'EmployeeQualificationLevel'])->name('emp-qualification-level');
Route::post('hr/addempqualificationlevel', [App\Http\Controllers\HRController::class, 'AddEmployeeQualificationLevel'])->name('AddEmployeeQualificationLevel');
Route::get('hr/empqualificationleveldata', [App\Http\Controllers\HRController::class, 'GetEmployeeQualificationLevelData'])->name('GetEmployeeQualificationLevelData');
Route::get('hr/empqualificationlevel-status', [App\Http\Controllers\HRController::class, 'UpdateEmployeeQualificationLevelStatus'])->name('UpdateEmployeeQualificationLevelStatus');
Route::get('hr/empqualificationlevelmodal/{id}', [App\Http\Controllers\HRController::class, 'UpdateEmployeeQualificationLevelModal'])->name('UpdateEmployeeQualificationLevelModal');
Route::post('hr/update-empqualificationlevel/{id}', [App\Http\Controllers\HRController::class, 'UpdateEmployeeQualificationLevelData'])->name('UpdateEmployeeQualificationLevelData');


Route::get('emp-cadre', [App\Http\Controllers\HRController::class, 'EmployeeCadre'])->name('emp-cadre');
Route::post('hr/addempcadre', [App\Http\Controllers\HRController::class, 'AddEmployeeCadre'])->name('AddEmployeeCadre');
Route::get('hr/empcadredata', [App\Http\Controllers\HRController::class, 'GetEmployeeCadreData'])->name('GetEmployeeCadreData');
Route::get('hr/empcadre-status', [App\Http\Controllers\HRController::class, 'UpdateEmployeeCadreStatus'])->name('UpdateEmployeeCadreStatus');
Route::get('hr/empcadreStatus/{id}', [App\Http\Controllers\HRController::class, 'UpdateEmployeeCadreModal'])->name('UpdateEmployeeCadreModal');
Route::post('hr/update-empcadre/{id}', [App\Http\Controllers\HRController::class, 'UpdateEmployeeCadreData'])->name('UpdateEmployeeCadreData');


Route::get('emp-position', [App\Http\Controllers\HRController::class, 'EmployeePosition'])->name('emp-position');
Route::post('hr/addempposition', [App\Http\Controllers\HRController::class, 'AddEmployeePosition'])->name('AddEmployeePosition');
Route::get('hr/emppositiondata', [App\Http\Controllers\HRController::class, 'GetEmployeePositionData'])->name('GetEmployeePositionData');
Route::get('hr/empposition-status', [App\Http\Controllers\HRController::class, 'UpdateEmployeePositionStatus'])->name('UpdateEmployeePositionStatus');
Route::get('hr/emppositionStatus/{id}', [App\Http\Controllers\HRController::class, 'UpdateEmployeePositionModal'])->name('UpdateEmployeePositionModal');
Route::post('hr/update-empposition/{id}', [App\Http\Controllers\HRController::class, 'UpdateEmployeePositionData'])->name('UpdateEmployeePositionData');
Route::get('hr/getcadre', [HRController::class, 'GetSelectedCadre'])->name('GetSelectedCadre');


Route::get('employee', [App\Http\Controllers\HRController::class, 'ViewEmployee'])->name('employee');
Route::post('hr/addemployee', [App\Http\Controllers\HRController::class, 'AddEmployee'])->name('AddEmployee');
Route::get('hr/viewemployee', [App\Http\Controllers\HRController::class, 'GetEmployeeData'])->name('GetEmployeeData');
Route::get('hr/employee-status', [App\Http\Controllers\HRController::class, 'UpdateEmployeeDetailStatus'])->name('UpdateEmployeeDetailStatus');
Route::get('hr/employeedetail/{id}', [App\Http\Controllers\HRController::class, 'EmployeeDetailModal'])->name('EmployeeDetailModal');
Route::get('hr/updatemodal/{id}', [App\Http\Controllers\HRController::class, 'UpdateEmployeeModal'])->name('UpdateEmployeeModal');
Route::post('edit-employee/{id}', [App\Http\Controllers\HRController::class, 'UpdateEmployee'])->name('UpdateEmployee');

Route::get('emp-salary', [App\Http\Controllers\HRController::class, 'EmployeeSalary'])->name('emp-salary');
Route::post('hr/addempsalary', [App\Http\Controllers\HRController::class, 'AddEmployeeSalary'])->name('AddEmployeeSalary');
Route::get('hr/viewemployeesalary', [App\Http\Controllers\HRController::class, 'GetEmployeeSalaryData'])->name('GetEmployeeSalaryData');
Route::get('hr/empsalary-status', [App\Http\Controllers\HRController::class, 'UpdateEmployeeSalaryStatus'])->name('UpdateEmployeeSalaryStatus');
Route::get('hr/updatesalarymodal/{id}', [App\Http\Controllers\HRController::class, 'UpdateEmployeeSalaryModal'])->name('UpdateEmployeeSalaryModal');
Route::post('hr/update-empsalary/{id}', [App\Http\Controllers\HRController::class, 'UpdateEmployeeSalary'])->name('UpdateEmployeeSalary');


Route::get('emp-qualification-setup', [App\Http\Controllers\HRController::class, 'EmployeeQualification'])->name('emp-qualification-setup');
Route::post('hr/addqualification-setup', [App\Http\Controllers\HRController::class, 'AddQualificationSetup'])->name('AddQualificationSetup');
Route::get('hr/viewqualification-setup/{id}', [App\Http\Controllers\HRController::class, 'ViewQualificationSetup'])->name('ViewQualificationSetup');
Route::post('hr/updatequalification-setup', [App\Http\Controllers\HRController::class, 'UpdateQualificationSetup'])->name('UpdateQualificationSetup');

Route::get('emp-documents', [App\Http\Controllers\HRController::class, 'EmployeeDocuments'])->name('emp-documents');
Route::post('hr/addempdocuments', [App\Http\Controllers\HRController::class, 'AddEmployeeDocuments'])->name('AddEmployeeDocuments');
Route::get('hr/viewempdocument/', [App\Http\Controllers\HRController::class, 'ViewEmployeeDocuments'])->name('ViewEmployeeDocuments');
Route::get('hr/empdocument-status', [App\Http\Controllers\HRController::class, 'UpdateEmployeeDocumentStatus'])->name('UpdateEmployeeDocumentStatus');
Route::get('hr/updateempdocuments/{id}', [App\Http\Controllers\HRController::class, 'UpdateEmployeeDocumentModal'])->name('UpdateEmployeeDocumentModal');
Route::post('hr/saveempdocuments', [App\Http\Controllers\HRController::class, 'saveEmployeeDocuments'])->name('saveEmployeeDocuments');

Route::get('emp-medical-license', [App\Http\Controllers\HRController::class, 'EmployeeMedicalLicense'])->name('emp-medical-license');
Route::post('hr/addmedical-license', [App\Http\Controllers\HRController::class, 'AddMedicalLicense'])->name('AddMedicalLicense');
Route::get('hr/viewmedical-license/{id}', [App\Http\Controllers\HRController::class, 'ViewMedicalLicense'])->name('ViewMedicalLicense');
Route::post('hr/updatemedical-license', [App\Http\Controllers\HRController::class, 'UpdateMedicalLicense'])->name('UpdateMedicalLicense');


Route::get('emp-costcenter', [App\Http\Controllers\HRController::class, 'EmployeeCostCenter'])->name('emp-costcenter');
Route::post('hr/addempcc', [App\Http\Controllers\HRController::class, 'AddEmployeeCostCenter'])->name('AddEmployeeCostCenter');
Route::get('hr/viewemp-cc/{id}', [App\Http\Controllers\HRController::class, 'ViewEmployeeCostCenter'])->name('ViewEmployeeCostCenter');
Route::post('hr/updateempcc', [App\Http\Controllers\HRController::class, 'UpdateEmployeeCC'])->name('UpdateEmployeeCC');
Route::post('hr/update-allocatedservice/{id}', [App\Http\Controllers\HRController::class, 'UpdateAllocatedService'])->name('UpdateAllocatedService');


Route::get('emp-serviceallocation', [App\Http\Controllers\HRController::class, 'EmployeeServiceAllocation'])->name('emp-serviceallocation');
Route::post('hr/allocateemp-service', [App\Http\Controllers\HRController::class, 'AllocateEmployeeService'])->name('AllocateEmployeeService');
Route::get('hr/viewallocatedservice', [App\Http\Controllers\HRController::class, 'GetAllocatedService'])->name('GetAllocatedService');
Route::get('hr/sa-status', [App\Http\Controllers\HRController::class, 'UpdateAllocatedServiceStatus'])->name('UpdateAllocatedServiceStatus');
Route::get('hr/serviceallocationmodal/{id}', [App\Http\Controllers\HRController::class, 'ServiceAllocationModal'])->name('ServiceAllocationModal');


Route::get('emp-locationallocation', [App\Http\Controllers\HRController::class, 'EmployeeLocationAllocation'])->name('emp-locationallocation');
Route::post('hr/allocateemp-location', [App\Http\Controllers\HRController::class, 'AllocateEmployeeServiceLocation'])->name('AllocateEmployeeServiceLocation');
// Route::get('hr/viewallocatedemplocation', [App\Http\Controllers\HRController::class, 'GetAllocatedEmpLocation'])->name('GetAllocatedEmpLocation');
// Route::get('hr/ela-status', [App\Http\Controllers\HRController::class, 'UpdateAllocatedEmpLocationStatus'])->name('UpdateAllocatedEmpLocationStatus');
Route::get('hr/viewemp-location/{id}', [App\Http\Controllers\HRController::class, 'ViewEmployeeAllocatedLocation'])->name('ViewEmployeeAllocatedLocation');
Route::post('hr/updateemplocation', [App\Http\Controllers\HRController::class, 'UpdateALlocatedEmployeeLocation'])->name('UpdateALlocatedEmployeeLocation');

// Route::get('hr/serviceallocationmodal/{id}', [App\Http\Controllers\HRController::class, 'ServiceAllocationModal'])->name('ServiceAllocationModal');


Route::get('hr/getgender', [HRController::class, 'GetSelectedGender'])->name('GetSelectedGender');
Route::get('hr/getposition', [HRController::class, 'GetSelectedPosition'])->name('GetSelectedPosition');
Route::get('hr/getqualification', [HRController::class, 'GetSelectedQualification'])->name('GetSelectedQualification');
Route::get('hr/getempstatus', [HRController::class, 'GetSelectedEmpStatus'])->name('GetSelectedEmpStatus');
Route::get('hr/getworkingstatus', [HRController::class, 'GetSelectedEmpWorkingStatus'])->name('GetSelectedEmpWorkingStatus');
Route::get('hr/getemployee', [HRController::class, 'GetSelectedEmployee'])->name('GetSelectedEmployee');
Route::get('hr/getorgemployee', [HRController::class, 'GetOrganizationEmployees'])->name('GetOrganizationEmployees');
Route::get('hr/getemployeedetails', [HRController::class, 'GetEmployeeDetails'])->name('GetEmployeeDetails');
Route::get('hr/getqualificationemployee', [HRController::class, 'GetQualificationEmployee'])->name('GetQualificationEmployee');
Route::get('hr/getsalaryemp', [HRController::class, 'GetSalaryEmployee'])->name('GetSalaryEmployee');
Route::get('hr/getmedicallicenseemployee', [HRController::class, 'GetMedicalLicenseEmployee'])->name('GetMedicalLicenseEmployee');
Route::get('hr/getcceemployee', [HRController::class, 'GetCCEmployee'])->name('GetCCEmployee');
Route::get('hr/getphysicians', [HRController::class, 'GetPhysicians'])->name('GetPhysicians');
Route::get('hr/getdocumentseemployee', [HRController::class, 'GetDocumentEmployees'])->name('GetDocumentEmployees');

Route::get('hr/getserviceeemployee', [HRController::class, 'GetServiceEmployee'])->name('GetServiceEmployee');
Route::get('hr/geteemployeeforlocation', [HRController::class, 'GetEmployeeForLocation'])->name('GetEmployeeForLocation');
//HR

// Inventory Setup
Route::get('inventory-category', [App\Http\Controllers\InventoryController::class, 'InventoryCategory'])->name('inventory-category');
Route::post('inventory/addinvcategory', [InventoryController::class, 'AddInventoryCategory'])->name('AddInventoryCategory');
Route::get('inventory/inventorycategory', [App\Http\Controllers\InventoryController::class, 'GetInventoryCategoryData'])->name('GetInventoryCategoryData');
Route::get('inventory/invcat-status', [App\Http\Controllers\InventoryController::class, 'UpdateInventoryCategoryStatus'])->name('UpdateInventoryCategoryStatus');
Route::get('inventory/updateinventorycategory/{id}', [App\Http\Controllers\InventoryController::class, 'UpdateInventoryCategoryModal'])->name('UpdateInventoryCategoryModal');
Route::post('inventory/update-inventorycategory/{id}', [App\Http\Controllers\InventoryController::class, 'UpdateInventoryCategory'])->name('UpdateInventoryCategory');

Route::get('inventory-subcategory', [App\Http\Controllers\InventoryController::class, 'InventorySubCategory'])->name('inventory-subcategory');
Route::post('inventory/addinvsubcategory', [InventoryController::class, 'AddInventorySubCategory'])->name('AddInventorySubCategory');
Route::get('inventory/invsubcategory', [App\Http\Controllers\InventoryController::class, 'GetInventorySubCategoryData'])->name('GetInventorySubCategoryData');
Route::get('inventory/invsubcat-status', [App\Http\Controllers\InventoryController::class, 'UpdateInventorySubCategoryStatus'])->name('UpdateInventorySubCategoryStatus');
Route::get('inventory/updateinvsubcategory/{id}', [App\Http\Controllers\InventoryController::class, 'UpdateInventorySubCategoryModal'])->name('UpdateInventorySubCategoryModal');
Route::post('inventory/update-invsubcategory/{id}', [App\Http\Controllers\InventoryController::class, 'UpdateInventorySubCategory'])->name('UpdateInventorySubCategory');
Route::get('inventory/getinvcat', [InventoryController::class, 'GetInventoryCategory'])->name('GetInventoryCategory');
Route::get('inventory/getselectedinvsubcat', [InventoryController::class, 'GetSelectedInventorySubCategory'])->name('GetSelectedInventorySubCategory');

Route::get('inventory-type', [App\Http\Controllers\InventoryController::class, 'InventoryType'])->name('inventory-type');
Route::post('inventory/addinvtype', [InventoryController::class, 'AddInventoryType'])->name('AddInventoryType');
Route::get('inventory/invtype', [App\Http\Controllers\InventoryController::class, 'GetInventoryTypeData'])->name('GetInventoryTypeData');
Route::get('inventory/invtype-status', [App\Http\Controllers\InventoryController::class, 'UpdateInventoryTypeStatus'])->name('UpdateInventoryTypeStatus');
Route::get('inventory/updateinvtype/{id}', [App\Http\Controllers\InventoryController::class, 'UpdateInventoryTypeModal'])->name('UpdateInventoryTypeModal');
Route::post('inventory/update-invtype/{id}', [App\Http\Controllers\InventoryController::class, 'UpdateInventoryType'])->name('UpdateInventoryType');

Route::get('purchase-order', [App\Http\Controllers\InventoryController::class, 'PurchaseOrder'])->name('purchase-order');
Route::post('inventory/addpurchaseorder', [InventoryController::class, 'AddPurchaseOrder'])->name('AddPurchaseOrder');
Route::get('inventory/purchaseorder', [App\Http\Controllers\InventoryController::class, 'GetPurchaseOrderData'])->name('GetPurchaseOrderData');
Route::get('inventory/purchaseorder-status', [App\Http\Controllers\InventoryController::class, 'UpdatePurchaseOrdertatus'])->name('UpdatePurchaseOrdertatus');
Route::get('inventory/updatepurchaseorder/{id}', [App\Http\Controllers\InventoryController::class, 'UpdatePurchaseOrderModal'])->name('UpdatePurchaseOrderModal');
Route::post('inventory/update-purchaseorder/{id}', [App\Http\Controllers\InventoryController::class, 'UpdatePurchaseOrder'])->name('UpdatePurchaseOrder');
Route::post('/inventory/approve-po', [App\Http\Controllers\InventoryController::class, 'ApprovePurchaseOrder'])->name('ApprovePurchaseOrder');
Route::get('/purchase-order/{id}/pdf', [InventoryController::class, 'generatePdfPO'])->name('purchaseOrder.pdf');


Route::get('work-order', [App\Http\Controllers\InventoryController::class, 'WorkOrder'])->name('work-order');
Route::post('inventory/addworkorder', [InventoryController::class, 'AddWorkOrder'])->name('AddWorkOrder');
Route::get('inventory/workorder', [App\Http\Controllers\InventoryController::class, 'GetWorkOrderData'])->name('GetWorkOrderData');
Route::get('inventory/workorder-status', [App\Http\Controllers\InventoryController::class, 'UpdateWorkOrdertatus'])->name('UpdateWorkOrdertatus');
Route::get('inventory/updateworkorder/{id}', [App\Http\Controllers\InventoryController::class, 'UpdateWorkOrderModal'])->name('UpdateWorkOrderModal');
Route::post('inventory/update-workorder/{id}', [App\Http\Controllers\InventoryController::class, 'UpdateWorkOrder'])->name('UpdateWorkOrder');
Route::post('/inventory/approve-wo', [App\Http\Controllers\InventoryController::class, 'ApproveWorkOrder'])->name('ApproveWorkOrder');
Route::get('/work-order/{id}/pdf', [InventoryController::class, 'generatePdfWO'])->name('workOrder.pdf');


// Route::get('inventory-management', [App\Http\Controllers\InventoryController::class, 'InventoryManagement'])->name('inventory-management');
// Route::post('inventory/addinvmanagement', [InventoryController::class, 'AddInventoryManagement'])->name('AddInventoryManagement');
// Route::get('inventory/invmanagement', [App\Http\Controllers\InventoryController::class, 'GetInventoryManagementData'])->name('GetInventoryManagementData');
// Route::get('inventory/updateinvmanagement/{id}', [App\Http\Controllers\InventoryController::class, 'UpdateInventoryManagementModal'])->name('UpdateInventoryManagementModal');
// Route::post('inventory/update-invmanagement/{id}', [App\Http\Controllers\InventoryController::class, 'UpdateInventoryManagement'])->name('UpdateInventoryManagement');
// Route::get('inventory/getprevioustransactions', [InventoryController::class, 'GetPreviousTransactions'])->name('GetPreviousTransactions');
// Route::get('inventory/getsiterequisition', [InventoryController::class, 'GetSiteRequisition'])->name('GetSiteRequisition');


Route::get('inventory/gettransactiontypeim', [InventoryController::class, 'GetTransactionTypeInventoryManagement'])->name('inventory/gettransactiontypeim');
Route::get('inventory/getmaterialtransactiontypes', [App\Http\Controllers\InventoryController::class, 'GetMaterialManagementTransactionTypes'])->name('GetMaterialManagementTransactionTypes');
Route::get('external-transaction', [App\Http\Controllers\InventoryController::class, 'ShowExternalTransaction'])->name('external-transaction');
Route::post('inventory/addexternaltransaction', [InventoryController::class, 'AddExternalTransaction'])->name('AddExternalTransaction');
Route::get('inventory/externaltransaction', [App\Http\Controllers\InventoryController::class, 'GetExternalTransactionData'])->name('GetExternalTransactionData');

Route::get('issue-dispense', [App\Http\Controllers\InventoryController::class, 'ShowIssueDispense'])->name('issue-dispense');
Route::get('patient/fetchpatientdetails', [App\Http\Controllers\PatientController::class, 'FetchpatientRecord'])->name('FetchpatientRecord');
Route::get('inventory/issuedispense', [App\Http\Controllers\InventoryController::class, 'GetIssueDispenseData'])->name('GetIssueDispenseData');
Route::get('inventory/respond-issuedispense', [App\Http\Controllers\InventoryController::class, 'RespondIssueDispense'])->name('RespondIssueDispense');
Route::post('inventory/addissuedispense', [InventoryController::class, 'AddIssueDispense'])->name('AddIssueDispense');

Route::get('material-transfer', [App\Http\Controllers\InventoryController::class, 'ShowMaterialTransfer'])->name('material-transfer');
Route::get('inventory/materialtransfer', [App\Http\Controllers\InventoryController::class, 'GetMaterialTransferData'])->name('GetMaterialTransferData');
Route::get('inventory/respond-materialtransfer', [App\Http\Controllers\InventoryController::class, 'RespondMaterialTransfer'])->name('RespondMaterialTransfer');
Route::post('inventory/addmaterialtransfer', [InventoryController::class, 'AddMaterialTransfer'])->name('AddMaterialTransfer');

Route::get('consumption', [App\Http\Controllers\InventoryController::class, 'ShowConsumedData'])->name('consumption');
Route::get('inventory/consumption', [App\Http\Controllers\InventoryController::class, 'GetConsumptionData'])->name('GetConsumptionData');
Route::get('inventory/respond-consumption', [App\Http\Controllers\InventoryController::class, 'RespondConsumption'])->name('RespondConsumption');
Route::post('inventory/addconsumption', [InventoryController::class, 'AddConsumption'])->name('AddConsumption');

Route::get('inventory-return', [App\Http\Controllers\InventoryController::class, 'ShowInventoryReturn'])->name('inventory-return');
Route::get('inventory/return', [App\Http\Controllers\InventoryController::class, 'GetReturnData'])->name('GetReturnData');
Route::get('inventory/respond-return', [App\Http\Controllers\InventoryController::class, 'RespondReturn'])->name('RespondReturn');
Route::post('inventory/addreturn', [InventoryController::class, 'AddReturn'])->name('AddReturn');

Route::get('inventory-generic', [App\Http\Controllers\InventoryController::class, 'InventoryGeneric'])->name('inventory-generic');
Route::post('inventory/addinvgeneric', [InventoryController::class, 'AddInventoryGeneric'])->name('AddInventoryGeneric');
Route::get('inventory/invgeneric', [App\Http\Controllers\InventoryController::class, 'GetInventoryGenericData'])->name('GetInventoryGenericData');
Route::get('inventory/invgeneric-status', [App\Http\Controllers\InventoryController::class, 'UpdateInventoryGenericStatus'])->name('UpdateInventoryGenericStatus');
Route::get('inventory/updateinvgeneric/{id}', [App\Http\Controllers\InventoryController::class, 'UpdateInventoryGenericModal'])->name('UpdateInventoryGenericModal');
Route::get('inventory/getselectedinventorytype', [InventoryController::class, 'GetSelectedInventoryType'])->name('GetSelectedInventoryType');
Route::post('inventory/update-invgeneric/{id}', [App\Http\Controllers\InventoryController::class, 'UpdateInventoryGeneric'])->name('UpdateInventoryTGeneric');
Route::get('inventory/getselectedinventorygeneric', [InventoryController::class, 'GetSelectedInventoryGeneric'])->name('GetSelectedInventoryGeneric');
Route::get('inventory/getinventorygenerics', [InventoryController::class, 'GetInventoryGenerics'])->name('GetInventoryGenerics');

Route::get('inventory-brand', [App\Http\Controllers\InventoryController::class, 'InventoryBrand'])->name('inventory-brand');
Route::post('inventory/addinvbrand', [InventoryController::class, 'AddInventoryBrand'])->name('AddInventoryBrand');
Route::get('inventory/invbrand', [App\Http\Controllers\InventoryController::class, 'GetInventoryBrandData'])->name('GetInventoryBrandData');
Route::get('inventory/invbrand-status', [App\Http\Controllers\InventoryController::class, 'UpdateInventoryBrandStatus'])->name('UpdateInventoryBrandStatus');
Route::get('inventory/showbrand', [App\Http\Controllers\InventoryController::class, 'ShowItemBrand'])->name('ShowItemBrand');
Route::get('inventory/updateinvbrand/{id}', [App\Http\Controllers\InventoryController::class, 'UpdateInventoryBrandModal'])->name('UpdateInventoryBrandModal');
Route::post('inventory/update-invbrand/{id}', [App\Http\Controllers\InventoryController::class, 'UpdateInventoryBrand'])->name('UpdateInventoryBrand');


Route::get('inventory-transaction-type', [App\Http\Controllers\InventoryController::class, 'InventoryTransactionType'])->name('inventory-transaction-type');
Route::post('inventory/addinvtransactiontype', [InventoryController::class, 'AddInventorytransactionType'])->name('AddInventorytransactionType');
Route::get('inventory/invtransactiontype', [App\Http\Controllers\InventoryController::class, 'GetInventoryTransactionTypeData'])->name('GetInventoryTransactionTypeData');
Route::get('inventory/invtransactiontype-status', [App\Http\Controllers\InventoryController::class, 'UpdateInventoryTransactionTypeStatus'])->name('UpdateInventoryTransactionTypeStatus');
Route::get('inventory/updateinvtransactiontype/{id}', [App\Http\Controllers\InventoryController::class, 'UpdateInventoryTransactionTypeModal'])->name('UpdateInventoryTransactionTypeModal');
Route::post('inventory/update-invtransactiontype/{id}', [App\Http\Controllers\InventoryController::class, 'UpdateInventoryTransactionType'])->name('UpdateInventoryTransactionType');


// Route::get('vendor-registration', [App\Http\Controllers\InventoryController::class, 'InventoryVendorRegistration'])->name('vendor-registration');
// Route::post('inventory/addvendorregistration', [InventoryController::class, 'VendorRegistration'])->name('VendorRegistration');
// Route::get('inventory/vendorregistration', [App\Http\Controllers\InventoryController::class, 'GetVendorRegistrationData'])->name('GetVendorRegistrationData');
// Route::get('inventory/vendor-status', [App\Http\Controllers\InventoryController::class, 'UpdateVenderStatus'])->name('UpdateVenderStatus');
// Route::get('inventory/updatevendorregistration/{id}', [App\Http\Controllers\InventoryController::class, 'UpdateVendorRegistrationModal'])->name('UpdateVendorRegistrationModal');
// Route::post('inventory/update-vendorregistration/{id}', [App\Http\Controllers\InventoryController::class, 'UpdateVendorRegistration'])->name('UpdateVendorRegistration');

Route::get('inventory/showvendor', [App\Http\Controllers\InventoryController::class, 'ShowVendors'])->name('ShowVendors');

Route::get('third-party-registration', [App\Http\Controllers\InventoryController::class, 'ThirdParty'])->name('third-party-registration');
Route::post('inventory/addthirdpartyregistration', [InventoryController::class, 'ThirdPartyRegistration'])->name('ThirdPartyRegistration');
Route::get('inventory/thirdpartyregistration', [App\Http\Controllers\InventoryController::class, 'GetThirdPartyRegistrationData'])->name('GetThirdPartyRegistrationData');
Route::get('inventory/tp-status', [App\Http\Controllers\InventoryController::class, 'UpdateThirdPartyStatus'])->name('UpdateThirdPartyStatus');
Route::get('inventory/updatetpregistration/{id}', [App\Http\Controllers\InventoryController::class, 'UpdateThirdPartyRegistrationModal'])->name('UpdateThirdPartyRegistrationModal');
Route::post('inventory/update-tpregistration/{id}', [App\Http\Controllers\InventoryController::class, 'UpdateThirdPartyRegistration'])->name('UpdateThirdPartyRegistration');

Route::get('medication-routes', [App\Http\Controllers\InventoryController::class, 'ViewMedicationRoutes'])->name('medication-routes');
Route::post('inventory/addmedicationroutes', [InventoryController::class, 'AddMedicationRoutes'])->name('AddMedicationRoutes');
Route::get('inventory/medicationroutes', [App\Http\Controllers\InventoryController::class, 'GetMedicationRouteData'])->name('GetMedicationRouteData');
Route::get('inventory/medicationroute-status', [App\Http\Controllers\InventoryController::class, 'UpdateMedicationRouteStatus'])->name('UpdateMedicationRouteStatus');
Route::get('inventory/updatemedicationroutes/{id}', [App\Http\Controllers\InventoryController::class, 'UpdateMedicationRoutesModal'])->name('UpdateMedicationRoutesModal');
Route::post('inventory/update-medicationRoute/{id}', [App\Http\Controllers\InventoryController::class, 'UpdateMedicationRoutes'])->name('UpdateMedicationRoutes');
Route::get('inventory/fetchmedicationroute/', [App\Http\Controllers\InventoryController::class, 'FetchMedicationRoutes'])->name('FetchMedicationRoutes');

Route::get('medication-frequency', [App\Http\Controllers\InventoryController::class, 'ViewMedicationFrequency'])->name('medication-frequency');
Route::post('inventory/addmedicationfrequency', [InventoryController::class, 'AddMedicationFrequency'])->name('AddMedicationFrequency');
Route::get('inventory/medicationfrequency', [App\Http\Controllers\InventoryController::class, 'GetMedicationFrequencyData'])->name('GetMedicationFrequencyData');
Route::get('inventory/medicationfrequency-status', [App\Http\Controllers\InventoryController::class, 'UpdateMedicationFrequencyStatus'])->name('UpdateMedicationFrequencyStatus');
Route::get('inventory/updatemedicationfrequency/{id}', [App\Http\Controllers\InventoryController::class, 'UpdateMedicationFrequencyModal'])->name('UpdateMedicationFrequencyModal');
Route::post('inventory/update-medicationfrequency/{id}', [App\Http\Controllers\InventoryController::class, 'UpdateMedicationFrequency'])->name('UpdateMedicationFrequency');
Route::get('inventory/fetchmedicationfrequency/', [App\Http\Controllers\InventoryController::class, 'FetchMedicationFrequency'])->name('FetchMedicationFrequency');

Route::get('material-consumption', [App\Http\Controllers\InventoryController::class, 'InventoryMaterialConsumption'])->name('material-consumption');
Route::get('inventory/gettransactiontypes', [App\Http\Controllers\InventoryController::class, 'GetTransactionTypes'])->name('GetTransactionTypes');
Route::post('inventory/addmaterialconsumption', [InventoryController::class, 'AddMaterialConsumptionRequisition'])->name('AddMaterialConsumptionRequisition');
Route::get('inventory/materialconsumption', [App\Http\Controllers\InventoryController::class, 'GetMaterialConsumptionData'])->name('GetMaterialConsumptionData');
Route::get('inventory/materialconsumption-status', [App\Http\Controllers\InventoryController::class, 'UpdateMaterialConsumptionStatus'])->name('UpdateMaterialConsumptionStatus');
Route::get('inventory/updatematerialconsumption/{id}', [App\Http\Controllers\InventoryController::class, 'UpdateMaterialConsumptionModal'])->name('UpdateMaterialConsumptionModal');
Route::post('inventory/update-materialconsumption/{id}', [App\Http\Controllers\InventoryController::class, 'UpdateMaterialConsumption'])->name('UpdateMaterialConsumption');

Route::get('req-material-transfer', [App\Http\Controllers\InventoryController::class, 'RequisitionMaterialTransfers'])->name('req-material-transfer');
Route::post('inventory/addreqmaterialtransfer', [InventoryController::class, 'AddRequisitionMaterialTransfers'])->name('AddRequisitionMaterialTransfers');
Route::get('inventory/reqmaterialtransfer', [App\Http\Controllers\InventoryController::class, 'GetRequisitionMaterialTransfersData'])->name('GetRequisitionMaterialTransfersData');
Route::get('inventory/reqmaterialtransfer-status', [App\Http\Controllers\InventoryController::class, 'UpdateRequisitionMaterialTransferStatus'])->name('UpdateRequisitionMaterialTransferStatus');
Route::get('inventory/updatereqmaterialtransfer/{id}', [App\Http\Controllers\InventoryController::class, 'UpdateRequisitionMaterialTransferModal'])->name('UpdateRequisitionMaterialTransferModal');
Route::post('inventory/update-reqmaterialtransfer/{id}', [App\Http\Controllers\InventoryController::class, 'UpdateRequisitionMaterialTransfer'])->name('UpdateRequisitionMaterialTransfer');


Route::get('inventory/getbatchno', [InventoryController::class, 'GetBatchNo'])->name('GetBatchNo');
// Route::get('inventory/getexpiryrate', [InventoryController::class, 'GetBatchExpiryRate'])->name('GetBatchExpiryRate');
Route::get('inventory/getorganizationgeneric', [InventoryController::class, 'GetOrgItemGeneric'])->name('GetOrgItemGeneric');
Route::get('inventory/getgenericbrand', [InventoryController::class, 'GetGenericBrand'])->name('GetGenericBrand');
Route::get('inventory/getinvcatconsumption', [InventoryController::class, 'GetInventoryCategoryConsumption'])->name('GetInventoryCategoryConsumption');


Route::get('consumption-group', [App\Http\Controllers\InventoryController::class, 'ViewConsumptionGroups'])->name('consumption-group');
Route::post('inventory/addconsumptiongroup', [InventoryController::class, 'AddConsumptionGroup'])->name('AddConsumptionGroup');
Route::get('inventory/viewconsumptiongroup', [App\Http\Controllers\InventoryController::class, 'GetConsumptionGroupData'])->name('GetConsumptionGroupData');
Route::get('inventory/cg-status', [App\Http\Controllers\InventoryController::class, 'UpdateConsumptionGroupStatus'])->name('UpdateConsumptionGroupStatus');
Route::get('inventory/updateconsumptiongroup/{id}', [App\Http\Controllers\InventoryController::class, 'UpdateConsumptionGroupModal'])->name('UpdateConsumptionGroupModal');
Route::post('inventory/update-consumptiongroup/{id}', [App\Http\Controllers\InventoryController::class, 'UpdateConsumptionGroup'])->name('UpdateConsumptionGroup');

Route::get('consumption-method', [App\Http\Controllers\InventoryController::class, 'ViewConsumptionMethods'])->name('consumption-method');
Route::post('inventory/addconsumptionmethod', [InventoryController::class, 'AddConsumptionMethod'])->name('AddConsumptionMethod');
Route::get('inventory/viewconsumptionmethod', [App\Http\Controllers\InventoryController::class, 'GetConsumptionMethodData'])->name('GetConsumptionMethodData');
Route::get('inventory/cm-status', [App\Http\Controllers\InventoryController::class, 'UpdateConsumptionMethodStatus'])->name('UpdateConsumptionMethodStatus');
Route::get('inventory/updateconsumptionmethod/{id}', [App\Http\Controllers\InventoryController::class, 'UpdateConsumptionMethodModal'])->name('UpdateConsumptionMethodModal');
Route::post('inventory/update-consumptionmethod/{id}', [App\Http\Controllers\InventoryController::class, 'UpdateConsumptionMethod'])->name('UpdateConsumptionMethod');

Route::get('stock-monitoring', [App\Http\Controllers\InventoryController::class, 'ViewStockMonitoring'])->name('stock-monitoring');
Route::post('inventory/addstockmonitoring', [InventoryController::class, 'AddStockMonitoring'])->name('AddStockMonitoring');
Route::get('inventory/viewstockmonitoring', [App\Http\Controllers\InventoryController::class, 'GetStockMonitoringData'])->name('GetStockMonitoringData');
Route::get('inventory/sm-status', [App\Http\Controllers\InventoryController::class, 'UpdateStockMonitoringStatus'])->name('UpdateStockMonitoringStatus');
Route::get('inventory/updatestockmonitoring/{id}', [App\Http\Controllers\InventoryController::class, 'UpdateStockMonitoringModal'])->name('UpdateStockMonitoringModal');
Route::post('inventory/update-stockmonitoring/{id}', [App\Http\Controllers\InventoryController::class, 'UpdateStockMonitoring'])->name('UpdateStockMonitoring');

Route::get('inventory-sourcedestination-type', [App\Http\Controllers\InventoryController::class, 'InventorySourceDestinationType'])->name('inventory-sourcedestination-type');
Route::post('inventory/addsourcedestinationtype', [InventoryController::class, 'AddInventorySourceDestinationType'])->name('AddInventorySourceDestinationType');
Route::get('inventory/viewsourcedestinationtype', [App\Http\Controllers\InventoryController::class, 'GetInventorySourceDestinationTypeData'])->name('GetInventorySourceDestinationTypeData');
Route::get('inventory/invsdt-status', [App\Http\Controllers\InventoryController::class, 'UpdateInventorySourceDestinationTypeStatus'])->name('UpdateInventorySourceDestinationTypeStatus');
Route::get('inventory/updatesourcedestinationtype/{id}', [App\Http\Controllers\InventoryController::class, 'UpdateInventorySourceDestinationTypeModal'])->name('UpdateInventorySourceDestinationTypeModal');
Route::post('inventory/update-sourcedestinationtype/{id}', [App\Http\Controllers\InventoryController::class, 'UpdateInventorySourceDestinationType'])->name('UpdateInventorySourceDestinationType');

Route::get('inventory-transaction-activity', [App\Http\Controllers\InventoryController::class, 'InventoryTransactionActivity'])->name('inventory-transaction-activity');
Route::post('inventory/addtransactionactivity', [InventoryController::class, 'AddInventoryTransactionActivity'])->name('AddInventoryTransactionActivity');
Route::get('inventory/viewtransactionactivity', [App\Http\Controllers\InventoryController::class, 'GetInventoryTransactionActivity'])->name('GetInventoryTransactionActivity');
Route::get('inventory/invta-status', [App\Http\Controllers\InventoryController::class, 'UpdateInventoryTransactionActivityStatus'])->name('UpdateInventoryTransactionActivityStatus');
Route::get('inventory/updatetransactionactivity/{id}', [App\Http\Controllers\InventoryController::class, 'UpdateInventoryTransactionActivityModal'])->name('UpdateInventoryTransactionActivityModal');
Route::post('inventory/update-transactionactivity/{id}', [App\Http\Controllers\InventoryController::class, 'UpdateInventoryTransactionActivity'])->name('UpdateInventoryTransactionActivity');

// Inventory Setup

//Patient Setup
Route::get('patient-registration', [App\Http\Controllers\PatientController::class, 'PatientRegistration'])->name('patient-registration');
Route::post('patient/addpatient', [PatientController::class, 'AddPatient'])->name('AddPatient');
Route::get('patient/patientdata', [App\Http\Controllers\PatientController::class, 'GetPatientData'])->name('GetPatientData');
Route::get('patient/patientdetail/{id}', [App\Http\Controllers\PatientController::class, 'PatientDetailModal'])->name('PatientDetailModal');
Route::get('patient/patient-status', [App\Http\Controllers\PatientController::class, 'UpdatePatientStatus'])->name('UpdatePatientStatus');
Route::get('patient/updatepatient/{id}', [App\Http\Controllers\PatientController::class, 'UpdatePatientModal'])->name('UpdatePatientModal');
Route::post('patient/update-patient/{id}', [App\Http\Controllers\PatientController::class, 'UpdatePatient'])->name('UpdatePatient');
Route::get('/patient/print-card/{id}', [App\Http\Controllers\PatientController::class, 'printPatientCard'])->name('patient.printCard');

//Patient Setup


//Patient Arrival & Departure Setup
Route::get('patient-inout', [App\Http\Controllers\PatientController::class, 'PatientArrivalDeparture'])->name('patient-inout');
// Route::get('patient/pad_details/{mr}', [App\Http\Controllers\PatientController::class, 'PatientArrivalDepartureDetail'])->name('PatientArrivalDepartureDetail');
Route::post('patient/addpatientarrival', [PatientController::class, 'AddPatientArrival'])->name('AddPatientArrival');
Route::get('patient/patientarrivaldeparture', [App\Http\Controllers\PatientController::class, 'GetPatientArrivalDepartureDetails'])->name('GetPatientArrivalDepartureDetails');
Route::get('patient/patientinout-status', [App\Http\Controllers\PatientController::class, 'UpdatePatientArrivalDepartureStatus'])->name('UpdatePatientArrivalDepartureStatus');
Route::post('patient/serviceend', [PatientController::class, 'EndService'])->name('EndService');
Route::get('patient/updatepatientinout/{id}', [App\Http\Controllers\PatientController::class, 'UpdatePatientInOutModal'])->name('UpdatePatientInOutModal');
Route::post('patient/update-patientinout/{id}', [App\Http\Controllers\PatientController::class, 'UpdatePatientInOut'])->name('UpdatePatientInOut');
//Patient Arrival & Departure Setup

Route::get('outsourced-services', [App\Http\Controllers\PatientController::class, 'OutsourcedServices'])->name('outsourced-services');
// Route::get('patient/pad_details/{mr}', [App\Http\Controllers\PatientController::class, 'PatientArrivalDepartureDetail'])->name('PatientArrivalDepartureDetail');
// Route::post('patient/addpatientarrival', [PatientController::class, 'AddPatientArrival'])->name('AddPatientArrival');
// Route::get('patient/patientarrivaldeparture', [App\Http\Controllers\PatientController::class, 'GetPatientArrivalDepartureDetails'])->name('GetPatientArrivalDepartureDetails');
// Route::get('patient/patientinout-status', [App\Http\Controllers\PatientController::class, 'UpdatePatientArrivalDepartureStatus'])->name('UpdatePatientArrivalDepartureStatus');
// Route::post('patient/serviceend', [PatientController::class, 'EndService'])->name('EndService');
// Route::get('patient/updatepatientinout/{id}', [App\Http\Controllers\PatientController::class, 'UpdatePatientInOutModal'])->name('UpdatePatientInOutModal');
// Route::post('patient/update-patientinout/{id}', [App\Http\Controllers\PatientController::class, 'UpdatePatientInOut'])->name('UpdatePatientInOut');
//Patient Arrival & Departure Setup

// Finance

// Chart Of Accounts Strategy
Route::get('account-strategy', [App\Http\Controllers\FinanceController::class, 'ChartOfAccountStrategy'])->name('account-strategy');
Route::post('finance/addaccountstrategy', [FinanceController::class, 'AddChartOfAccountStrategy'])->name('AddChartOfAccountStrategy');
Route::get('finance/accountstrategydata', [App\Http\Controllers\FinanceController::class, 'GetChartOfAccountStrategyData'])->name('GetChartOfAccountStrategyData');
Route::get('finance/accountstrategy-status', [App\Http\Controllers\FinanceController::class, 'UpdateChartOfAccountStrategyStatus'])->name('UpdateChartOfAccountStrategyStatus');
Route::get('finance/updateaccountstrategy/{id}', [App\Http\Controllers\FinanceController::class, 'UpdateChartOfAccountStrategyModal'])->name('UpdateChartOfAccountStrategyModal');
Route::post('finance/update-accountstrategy/{id}', [App\Http\Controllers\FinanceController::class, 'UpdateChartOfAccountStrategy'])->name('UpdateChartOfAccountStrategy');
// Chart Of Accounts Strategy

// Chart Of Accounts Strategy Setup
Route::get('account-strategy-setup', [App\Http\Controllers\FinanceController::class, 'ChartOfAccountStrategySetup'])->name('account-strategy-setup');
Route::post('finance/addaccountstrategysetup', [FinanceController::class, 'AddChartOfAccountStrategySetup'])->name('AddChartOfAccountStrategySetup');
Route::get('finance/accountstrategysetupdata', [App\Http\Controllers\FinanceController::class, 'GetChartOfAccountStrategySetupData'])->name('GetChartOfAccountStrategySetupData');
Route::get('finance/accountstrategysetup-status', [App\Http\Controllers\FinanceController::class, 'UpdateChartOfAccountStrategySetupStatus'])->name('UpdateChartOfAccountStrategySetupStatus');
Route::get('finance/updateaccountstrategysetup/{id}', [App\Http\Controllers\FinanceController::class, 'UpdateChartOfAccountStrategySetupModal'])->name('UpdateChartOfAccountStrategySetupModal');
Route::post('finance/update-accountstrategysetup/{id}', [App\Http\Controllers\FinanceController::class, 'UpdateChartOfAccountStrategySetup'])->name('UpdateChartOfAccountStrategySetup');
Route::get('finance/getaccountstrategy', [App\Http\Controllers\FinanceController::class, 'GetSelectedAccountStrategies'])->name('GetSelectedAccountStrategies');
Route::get('finance/getaccountstrategyorg', [App\Http\Controllers\FinanceController::class, 'GetSelectedAccountStrategyOrg'])->name('GetSelectedAccountStrategyOrg');
// Chart Of Accounts Strategy Setup

// SetUp Account Level
Route::post('finance/setupaccountlevel', [FinanceController::class, 'SetupAccountLevel'])->name('SetupAccountLevel');
Route::get('finance/accountleveloptions/{level}/{strategyId}', [App\Http\Controllers\FinanceController::class, 'GetAccountLevelOption'])->name('GetAccountLevelOption');
Route::get('finance/viewaccountlevels', [FinanceController::class, 'ViewAccountLevels'])->name('ViewAccountLevels');
Route::get('finance/getaccountleveldata/{strategyId}/{currentlevel}', [App\Http\Controllers\FinanceController::class, 'GetAccountLevelData'])->name('GetAccountLevelData');
Route::get('finance/getchildlevel/{strategyId}/{colmn}/{currentlevel}', [App\Http\Controllers\FinanceController::class, 'GetChildLevel'])->name('GetChildLevel');

Route::get('finance/getaccountnames', [FinanceController::class, 'GetAccountNames'])->name('GetAccountNames');

// SetUp Account Level

// Transaction Sources or Destinations
Route::get('transaction-source-destination', [App\Http\Controllers\FinanceController::class, 'TransactionSourceDestinations'])->name('transaction-source-destination');
Route::post('finance/addtransactionsd', [FinanceController::class, 'AddTransactionSourcesDestinations'])->name('AddTransactionSourcesDestinations');
Route::get('finance/transactionsddata', [App\Http\Controllers\FinanceController::class, 'GetTransactionSourcesDestinationsData'])->name('GetTransactionSourcesDestinationsData');
Route::get('finance/transactionsd-status', [App\Http\Controllers\FinanceController::class, 'UpdateTransactionSourcesDestinationsStatus'])->name('UpdateTransactionSourcesDestinationsStatus');
Route::get('finance/updateransactionsd/{id}', [App\Http\Controllers\FinanceController::class, 'UpdateTransactionSourcesDestinationsModal'])->name('UpdateTransactionSourcesDestinationsModal');
Route::post('finance/update-transactionsd/{id}', [App\Http\Controllers\FinanceController::class, 'UpdateTransactionSourcesDestinations'])->name('UpdateTransactionSourcesDestinations');

Route::get('finance/getsourcedestination', [FinanceController::class, 'GetTransactionSourceDestinations'])->name('GetTransactionSourceDestinations');
Route::get('finance/gettransactiontypes', [FinanceController::class, 'GetFinanceTransactionTypes'])->name('GetFinanceTransactionTypes');

// Transaction Sources or Destinations

// Financial Ledger Types
Route::get('financial-ledger-types', [App\Http\Controllers\FinanceController::class, 'FinancialLedgerTypes'])->name('financial-ledger-types');
Route::post('finance/addfinacialLedgertype', [FinanceController::class, 'AddFinancialLedgerTypes'])->name('AddFinancialLedgerTypes');
Route::get('finance/financialledgertypedata', [App\Http\Controllers\FinanceController::class, 'GetFinancialLedgerTypesData'])->name('GetFinancialLedgerTypesData');
Route::get('finance/financialledgertype-status', [App\Http\Controllers\FinanceController::class, 'UpdateFinancialLedgerTypesStatus'])->name('UpdateFinancialLedgerTypesStatus');
Route::get('finance/updatefinancialledgertype/{id}', [App\Http\Controllers\FinanceController::class, 'UpdateFinancialLedgerTypesModal'])->name('UpdateFinancialLedgerTypesModal');
Route::post('finance/update-financialledger/{id}', [App\Http\Controllers\FinanceController::class, 'UpdateFinancialLedgerTypes'])->name('UpdateFinancialLedgerTypes');

Route::get('finance/getfinancialledger', [FinanceController::class, 'GetFinancialLedgerTypes'])->name('GetFinancialLedgerTypes');

// Financial Ledger Types

// Financial Payroll Addition
Route::get('financial-payroll-addition', [App\Http\Controllers\FinanceController::class, 'PayrollAddition'])->name('financial-payroll-addition');
Route::post('finance/addpayrolladdition', [FinanceController::class, 'AddPayrollAddition'])->name('AddPayrollAddition');
Route::get('finance/payrolladditiondata', [App\Http\Controllers\FinanceController::class, 'GetPayrollAdditionData'])->name('GetPayrollAdditionData');
Route::get('finance/payrolladdition-status', [App\Http\Controllers\FinanceController::class, 'UpdatePayrollAdditionStatus'])->name('UpdatePayrollAdditionStatus');
Route::get('finance/updatepayrolladdition/{id}', [App\Http\Controllers\FinanceController::class, 'UpdatePayrollAdditionModal'])->name('UpdatePayrollAdditionModal');
Route::post('finance/update-payrolladdition/{id}', [App\Http\Controllers\FinanceController::class, 'UpdatePayrollAddition'])->name('UpdatePayrollAddition');
// Financial Payroll Addition

// Financial Payroll Deduction
Route::get('financial-payroll-deduction', [App\Http\Controllers\FinanceController::class, 'PayrollDeduction'])->name('financial-payroll-deduction');
Route::post('finance/addpayrolldeduction', [FinanceController::class, 'AddPayrollDeduction'])->name('AddPayrollDeduction');
Route::get('finance/payrolldeductiondata', [App\Http\Controllers\FinanceController::class, 'GetPayrollDeductionData'])->name('GetPayrollDeductionData');
Route::get('finance/payrolldeduction-status', [App\Http\Controllers\FinanceController::class, 'UpdatePayrollDeductionStatus'])->name('UpdatePayrollDeductionStatus');
Route::get('finance/updatepayrolldeduction/{id}', [App\Http\Controllers\FinanceController::class, 'UpdatePayrollDeductionModal'])->name('UpdatePayrollDeductionModal');
Route::post('finance/update-payrolldeduction/{id}', [App\Http\Controllers\FinanceController::class, 'UpdatePayrollDeduction'])->name('UpdatePayrollDeduction');
// Financial Payroll Deduction

// Financial Transaction Type
Route::get('finance-transaction-type', [App\Http\Controllers\FinanceController::class, 'FinanceTransactionType'])->name('finance-transaction-type');
Route::post('finance/addtransactiontype', [FinanceController::class, 'AddFinanceTransactionType'])->name('AddFinanceTransactionType');
Route::get('finance/financetransactiontypedata', [App\Http\Controllers\FinanceController::class, 'FinanceTransactionTypeData'])->name('FinanceTransactionTypeData');
Route::get('finance/transactiontype-status', [App\Http\Controllers\FinanceController::class, 'UpdateTransactionTypeStatus'])->name('UpdateTransactionTypeStatus');
Route::get('finance/updatefinancetransactiontype/{id}', [App\Http\Controllers\FinanceController::class, 'UpdateTransactionTypeModal'])->name('UpdateTransactionTypeModal');
Route::post('finance/update-financetransactiontype/{id}', [App\Http\Controllers\FinanceController::class, 'UpdateFinanceTransactiontype'])->name('UpdateFinanceTransactiontype');
// Financial Transaction Type

// Donors Registration
// Route::get('donor-registration', [App\Http\Controllers\FinanceController::class, 'RegisterDonor'])->name('donor-registration');
// Route::post('finance/donor_registration', [FinanceController::class, 'DonorRegistration'])->name('DonorRegistration');
// Route::get('finance/donorsdata', [App\Http\Controllers\FinanceController::class, 'GetDonorsData'])->name('GetDonorsData');
// Route::get('finance/donor-status', [App\Http\Controllers\FinanceController::class, 'UpdateDonorStatus'])->name('UpdateDonorStatus');
// Route::get('finance/updatedonor/{id}', [App\Http\Controllers\FinanceController::class, 'UpdateDonorModal'])->name('UpdateDonorModal');
// Route::post('finance/update-donor/{id}', [App\Http\Controllers\FinanceController::class, 'UpdateDonor'])->name('UpdateDonor');
// Donors Registration

// Financial Receiving
Route::get('finance-receiving', [App\Http\Controllers\FinanceController::class, 'FinanceReceiving'])->name('finance-receiving');
Route::post('finance/addfinancereceiving', [FinanceController::class, 'AddFinanceReceiving'])->name('AddFinanceReceiving');
Route::get('finance/financereceiving', [App\Http\Controllers\FinanceController::class, 'FinanceReceivingData'])->name('FinanceReceivingData');
Route::get('finance/financetransaction-status', [App\Http\Controllers\FinanceController::class, 'UpdateFinanceReceivingStatus'])->name('UpdateFinanceReceivingStatus');
Route::get('finance/updatefinancetransaction/{id}', [App\Http\Controllers\FinanceController::class, 'UpdateFinanceReceivingModal'])->name('UpdateFinanceReceivingModal');
Route::post('finance/update-financetransaction/{id}', [App\Http\Controllers\FinanceController::class, 'UpdateFinanceReceiving'])->name('UpdateFinanceReceiving');
Route::get('finance/checkfinancetransactiontype', [App\Http\Controllers\FinanceController::class, 'CheckFinanceTransactionType'])->name('CheckFinanceTransactionType');
// Financial Receiving

// Financial Payments
Route::get('finance-payment', [App\Http\Controllers\FinanceController::class, 'FinancePayments'])->name('finance-payment');
Route::post('finance/addfinancepayment', [FinanceController::class, 'AddFinancePayment'])->name('AddFinancePayment');
Route::get('finance/financepayment', [App\Http\Controllers\FinanceController::class, 'FinancePaymentData'])->name('FinancePaymentData');
Route::get('finance/financepayment-status', [App\Http\Controllers\FinanceController::class, 'UpdateFinancePaymentStatus'])->name('UpdateFinancePaymentStatus');
Route::get('finance/updatefinancepayment/{id}', [App\Http\Controllers\FinanceController::class, 'UpdateFinancePaymentModal'])->name('UpdateFinancePaymentModal');
Route::post('finance/update-financepayment/{id}', [App\Http\Controllers\FinanceController::class, 'UpdateFinancePayment'])->name('UpdateFinancePayment');
// Financial Payments

// Item Rates
Route::get('item-rates', [App\Http\Controllers\FinanceController::class, 'ItemRates'])->name('item-rates');
Route::post('finance/additemrates', [FinanceController::class, 'AddItemRates'])->name('AddItemRates');
Route::get('finance/itemrate', [App\Http\Controllers\FinanceController::class, 'ItemRatesData'])->name('ItemRatesData');
Route::get('finance/itemrate-status', [App\Http\Controllers\FinanceController::class, 'UpdateItemRatesStatus'])->name('UpdateItemRatesStatus');
Route::get('finance/updateitemrate/{id}', [App\Http\Controllers\FinanceController::class, 'UpdateItemRateModal'])->name('UpdateItemRateModal');
Route::post('finance/update-itemrate/{id}', [App\Http\Controllers\FinanceController::class, 'UpdateItemRate'])->name('UpdateItemRate');
// Item Rates

// Service Rates
Route::get('service-rates', [App\Http\Controllers\FinanceController::class, 'ServiceRates'])->name('service-rates');
Route::post('finance/fetch-servicerates', [FinanceController::class, 'FetchServiceRates'])->name('FetchServiceRates');
Route::post('finance/addservicerates', [FinanceController::class, 'AddServiceRates'])->name('AddServiceRates');
Route::get('finance/updateservicerates/{id}', [App\Http\Controllers\FinanceController::class, 'UpdateServiceRatesModal'])->name('UpdateServiceRatesModal');
Route::post('finance/update-servicerates/{id}', [App\Http\Controllers\FinanceController::class, 'UpdateServiceRates'])->name('UpdateServiceRates');
// Service Rates

// Finance


// Modules
Route::get('modules', [App\Http\Controllers\ModuleController::class, 'ShowModules'])->name('modules');
Route::post('module/addmodule', [ModuleController::class, 'AddModule'])->name('AddModule');
Route::get('module/viewmodule', [App\Http\Controllers\ModuleController::class, 'GetModuleData'])->name('GetModuleData');
// Modules


// Patient Medical Record
Route::get('icd-coding', [App\Http\Controllers\PatientMedicalRecord::class, 'ICDCoding'])->name('icd-coding');
Route::get('medicalrecord/getdiagnosisicdcode', [PatientMedicalRecord::class, 'GetDiagnosisICDCodes'])->name('GetDiagnosisICDCodes');
Route::get('medicalrecord/getsymptomsicdcode', [PatientMedicalRecord::class, 'GetProcedureICDCodes'])->name('GetProcedureICDCodes');
Route::post('medicalrecord/addicdcode', [PatientMedicalRecord::class, 'AddICDCoding'])->name('AddICDCoding');
Route::get('medicalrecord/viewicdcode', [App\Http\Controllers\PatientMedicalRecord::class, 'GetICDCodeData'])->name('GetICDCodeData');
Route::get('medicalrecord/icdcode-status', [App\Http\Controllers\PatientMedicalRecord::class, 'UpdateICDCodeStatus'])->name('UpdateICDCodeStatus');
Route::get('medicalrecord/updateicdcode/{id}', [App\Http\Controllers\PatientMedicalRecord::class, 'UpdateICDCodeModal'])->name('UpdateICDCodeModal');
Route::post('medicalrecord/update-icdcode/{id}', [App\Http\Controllers\PatientMedicalRecord::class, 'UpdateICDCode'])->name('UpdateICDCode');

Route::get('vital-sign', [App\Http\Controllers\PatientMedicalRecord::class, 'VitalSigns'])->name('vital-sign');
Route::get('medicalrecord/patient-record/{mr}', [App\Http\Controllers\PatientMedicalRecord::class, 'PatientRecords'])->name('PatientRecords');
Route::post('medicalrecord/addvitalsign', [PatientMedicalRecord::class, 'AddVitalSign'])->name('AddVitalSign');
Route::get('medicalrecord/viewvitalsign/{id}', [App\Http\Controllers\PatientMedicalRecord::class, 'GetVitalSignData'])->name('GetVitalSignData');
Route::get('medicalrecord/viewlatestvitalsign/{id}', [App\Http\Controllers\PatientMedicalRecord::class, 'GetLatestVitalSignData'])->name('GetLatestVitalSignData');
Route::get('medicalrecord/vitalsign-status', [App\Http\Controllers\PatientMedicalRecord::class, 'UpdateVitalSignStatus'])->name('UpdateVitalSignStatus');
Route::get('medicalrecord/updatevitalsign/{id}', [App\Http\Controllers\PatientMedicalRecord::class, 'UpdateVitalSignModal'])->name('UpdateVitalSignModal');
Route::post('medicalrecord/update-vitalsign/{id}', [App\Http\Controllers\PatientMedicalRecord::class, 'UpdateVitalSign'])->name('UpdateVitalSign');


Route::get('encounters-procedures', [App\Http\Controllers\PatientMedicalRecord::class, 'EncountersProcedures'])->name('encounters-procedures');
Route::post('medicalrecord/adddiagnosishistory', [PatientMedicalRecord::class, 'AddDiagnosisHistory'])->name('AddDiagnosisHistory');
Route::get('medicalrecord/viewmedicaldiagnosis/{id}', [App\Http\Controllers\PatientMedicalRecord::class, 'GetMedicalDiagnosisData'])->name('GetMedicalDiagnosisData');
Route::post('medicalrecord/addallergieshistory', [PatientMedicalRecord::class, 'AddAllergiesHistory'])->name('AddAllergiesHistory');
Route::get('medicalrecord/viewallergieshistory/{id}', [App\Http\Controllers\PatientMedicalRecord::class, 'GetAllergiesHistoryData'])->name('GetAllergiesHistoryData');
Route::post('medicalrecord/addimmunizationhistory', [PatientMedicalRecord::class, 'AddImmunizationHistory'])->name('AddImmunizationHistory');
Route::get('medicalrecord/viewimmunizationhistory/{id}', [App\Http\Controllers\PatientMedicalRecord::class, 'GetImmunizationHistoryData'])->name('GetImmunizationHistoryData');
Route::post('medicalrecord/adddrughistory', [PatientMedicalRecord::class, 'AddDrugHistory'])->name('AddDrugHistory');
Route::get('medicalrecord/viewdrughistory/{id}', [App\Http\Controllers\PatientMedicalRecord::class, 'GetDrugHistoryData'])->name('GetDrugHistoryData');
Route::post('medicalrecord/addpasthistory', [PatientMedicalRecord::class, 'AddPastHistory'])->name('AddPastHistory');
Route::get('medicalrecord/viewpasthistory/{id}', [App\Http\Controllers\PatientMedicalRecord::class, 'GetPastHistoryData'])->name('GetPastHistoryData');
Route::post('medicalrecord/addobsterichistory', [PatientMedicalRecord::class, 'AddObstericHistory'])->name('AddObstericHistory');
Route::get('medicalrecord/viewobsterichistory/{id}', [App\Http\Controllers\PatientMedicalRecord::class, 'GetObstericHistoryData'])->name('GetObstericHistoryData');
Route::post('medicalrecord/addsocialhistory', [PatientMedicalRecord::class, 'AddSocialHistory'])->name('AddSocialHistory');
Route::get('medicalrecord/viewsocialhistory/{id}', [App\Http\Controllers\PatientMedicalRecord::class, 'GetSocialHistoryData'])->name('GetSocialHistoryData');
Route::post('medicalrecord/addvisitbaseddetails', [PatientMedicalRecord::class, 'AddVisitBasedDetails'])->name('AddVisitBasedDetails');
Route::get('medicalrecord/viewvisitbaseddetails/{id}', [App\Http\Controllers\PatientMedicalRecord::class, 'GetVisitBasedDetails'])->name('GetVisitBasedDetails');
Route::get('medicalrecord/gettrackingvisit/{id}', [App\Http\Controllers\PatientMedicalRecord::class, 'GetTrackingVisits'])->name('GetTrackingVisits');


Route::post('medicalrecord/addreqepi', [PatientMedicalRecord::class, 'AddRequisitionEPI'])->name('AddRequisitionEPI');
Route::get('medicalrecord/viewreqepi', [App\Http\Controllers\PatientMedicalRecord::class, 'GetRequisitionEPI'])->name('GetRequisitionEPI');
Route::get('medicalrecord/reqepi-status', [App\Http\Controllers\PatientMedicalRecord::class, 'UpdateRequisitionEPIStatus'])->name('UpdateRequisitionEPIStatus');
Route::get('medicalrecord/updatereqepi/{id}', [App\Http\Controllers\PatientMedicalRecord::class, 'UpdateReqEPIModal'])->name('UpdateReqEPIModal');
Route::post('/medicalrecord/update-reqepi/{id}', [App\Http\Controllers\PatientMedicalRecord::class, 'UpdateReqEPI'])->name('UpdateReqEPI');


Route::get('req-medication-consumption/{mr}', [App\Http\Controllers\PatientMedicalRecord::class, 'RequisitionMedicationConsumption'])->name('req-medication-consumption');
Route::post('medicalrecord/addrmc', [PatientMedicalRecord::class, 'AddRequisitionMedicationConsumption'])->name('AddRequisitionMedicationConsumption');
Route::get('medicalrecord/viewreqmc/{mr}', [App\Http\Controllers\PatientMedicalRecord::class, 'GetRequisitionMedicationConsumption'])->name('GetRequisitionMedicationConsumption');
Route::get('medicalrecord/reqmc-status', [App\Http\Controllers\PatientMedicalRecord::class, 'UpdateRequisitionMedicationConsumptionStatus'])->name('UpdateRequisitionMedicationConsumptionStatus');
Route::get('medicalrecord/updatereqmc/{id}', [App\Http\Controllers\PatientMedicalRecord::class, 'UpdateReqMedicationConsumptionModal'])->name('UpdateReqMedicationConsumptionModal');
Route::post('/medicalrecord/update-reqmc/{id}', [App\Http\Controllers\PatientMedicalRecord::class, 'UpdateReqMedicationConsumption'])->name('UpdateReqMedicationConsumption');

Route::get('investigationtracking/{mr}', [App\Http\Controllers\PatientMedicalRecord::class, 'ShowInvestigationTracking'])->name('investigationtracking');
Route::post('medicalrecord/sampleconfirmationdate', [PatientMedicalRecord::class, 'ConfirmSampleReport'])->name('ConfirmSampleReport');
Route::post('medicalrecord/uploadreport', [PatientMedicalRecord::class, 'UploadReport'])->name('UploadReport');
Route::get('medicalrecord/viewinvestigationTracking/{mr}', [App\Http\Controllers\PatientMedicalRecord::class, 'GetInvestigationTrackingData'])->name('GetInvestigationTrackingData');
Route::get('medicalrecord/get-patients-for-sidebar', [App\Http\Controllers\PatientMedicalRecord::class, 'GetPatientsForSidebar'])->name('get-patients-for-sidebar');
// Route::get('medicalrecord/reqmc-status', [App\Http\Controllers\PatientMedicalRecord::class, 'UpdateRequisitionMedicationConsumptionStatus'])->name('UpdateRequisitionMedicationConsumptionStatus');
// Route::get('medicalrecord/updatereqmc/{id}', [App\Http\Controllers\PatientMedicalRecord::class, 'UpdateReqMedicationConsumptionModal'])->name('UpdateReqMedicationConsumptionModal');
// Route::post('/medicalrecord/update-reqmc/{id}', [App\Http\Controllers\PatientMedicalRecord::class, 'UpdateReqMedicationConsumption'])->name('UpdateReqMedicationConsumption');


Route::get('procedure-coding', [App\Http\Controllers\PatientMedicalRecord::class, 'ShowProcedureCoding'])->name('procedure-coding');
Route::get('activation/viewprocedurecoding', [App\Http\Controllers\PatientMedicalRecord::class, 'ViewProcedureCoding'])->name('ViewProcedureCoding');
Route::get('activation/getproceduredmedicalcodes', [PatientMedicalRecord::class, 'GetProcedureMedicalCoding'])->name('activation/getproceduredmedicalcodes');
Route::post('activation/activateprocedurcoding', [App\Http\Controllers\PatientMedicalRecord::class, 'InsertUpdateProcedureMedicalCoding'])->name('InsertUpdateProcedureMedicalCoding');


Route::post('medicalrecord/addpatientattachment', [PatientMedicalRecord::class, 'AddPatientAttachement'])->name('AddPatientAttachement');
Route::get('medicalrecord/viewpatientattachment/{id}', [App\Http\Controllers\PatientMedicalRecord::class, 'GetPatientAttachments'])->name('GetPatientAttachments');

// Route::get('medicalrecord/viewreqepi', [App\Http\Controllers\PatientMedicalRecord::class, 'GetRequisitionEPI'])->name('GetRequisitionEPI');
// Route::get('medicalrecord/reqepi-status', [App\Http\Controllers\PatientMedicalRecord::class, 'UpdateRequisitionEPIStatus'])->name('UpdateRequisitionEPIStatus');
// Route::get('medicalrecord/updatereqepi/{id}', [App\Http\Controllers\PatientMedicalRecord::class, 'UpdateReqEPIModal'])->name('UpdateReqEPIModal');
// Route::post('/medicalrecord/update-reqepi/{id}', [App\Http\Controllers\PatientMedicalRecord::class, 'UpdateReqEPI'])->name('UpdateReqEPI');

// Patient Medical Record

// Inventory Reports
Route::get('inventory-report', [App\Http\Controllers\ReportController::class, 'InventoryReport'])->name('inventory-report');
Route::get('inventory-report/get-data', [App\Http\Controllers\ReportController::class, 'getInventoryReportData'])->name('inventory-report-get-data');
Route::post('inventory-report/download-pdf', [App\Http\Controllers\ReportController::class, 'downloadInventoryReportPDF'])->name('inventory-report-download-pdf');
Route::get('inventory/getbatchnoreporting', [InventoryController::class, 'GetBatchNoForReporting'])->name('GetBatchNoForReporting');

// Inventory Reports