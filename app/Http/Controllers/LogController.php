<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Logs;
use Illuminate\Support\Facades\Auth;

class LogController extends Controller
{
    private $currentDatetime;
    private $sessionUser;
    private $roles;
    private $rights;
    private $assignedSites;
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->currentDatetime = Carbon::now('Asia/Karachi')->timestamp;
            $this->sessionUser = session('user');
            $this->roles = session('role');
            $this->rights = session('rights');
            $this->assignedSites = session('sites');
            // if (Auth::check() && Auth::user()->role_id == 1) {
            if (Auth::check()) {
                return $next($request);
            } else {
                return redirect('/');
            }
        });
    }

    public function ViewLogs($id, Request $request)
    {
        $logIds = explode(',', $id);
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 10);
        
        $logs = DB::table('logs')
                ->whereIn('id', $logIds)
                ->orderByDesc('id')
                ->offset($offset)
                ->limit($limit)
                ->get();

        if ($logs->isNotEmpty()) {
            $data = [];
            foreach ($logs as $log) {
                // Decode JSON fields
                $summary = $log->summary ? json_decode($log->summary, true) : null;
                $previousData = $log->previous_data ? json_decode($log->previous_data, true) : null;
                $newData = $log->new_data ? json_decode($log->new_data, true) : null;
                
                // Transform status values from integer to text
                $previousData = $this->transformStatusValues($previousData);
                $newData = $this->transformStatusValues($newData);
                
                // Format timestamp with seconds
                $timestamp = Carbon::createFromTimestamp($log->timestamp);
                $timestamp = $timestamp->format('l d F Y - h:i:s A');
                
                $data[] = [
                    'module' => $log->module,
                    'event' => $log->event,
                    'summary' => $summary,
                    'previous_data' => $previousData,
                    'new_data' => $newData,
                    'timestamp' => $timestamp,
                    'user_id' => $log->user_id,
                    'record_id' => $log->record_id,
                ];
            }

            return response()->json([
                'data' => $data,
                'has_more' => count($logs) >= $limit
            ]);
        }
        else {
            return response()->json(['error' => 'Logs not found'], 404);
        }
    }

    /**
     * Transform integer/ID values to readable text
     * 
     * @param mixed $data
     * @return mixed
     */
    private function transformStatusValues($data)
    {
        if ($data === null) {
            return null;
        }
        
        if (is_array($data)) {
            $transformed = [];
            foreach ($data as $key => $value) {
                $lowerKey = strtolower($key);
                $transformedValue = null;
                
                // Handle status fields
                if ($lowerKey === 'status') {
                    $transformedValue = $this->transformStatus($value);
                }
                // Handle role_id
                elseif ($lowerKey === 'role_id' || $lowerKey === 'role') {
                    $transformedValue = $this->transformRoleId($value);
                }
                // Handle org_id
                elseif ($lowerKey === 'org_id' || $lowerKey === 'organization_id') {
                    $transformedValue = $this->transformOrgId($value);
                }
                // Handle emp_id (employee)
                elseif ($lowerKey === 'emp_id' || $lowerKey === 'employee_id') {
                    $transformedValue = $this->transformEmpId($value);
                }
                // Handle site_id
                elseif ($lowerKey === 'site_id') {
                    $transformedValue = $this->transformSiteId($value);
                }
                // Handle prefix_id
                elseif ($lowerKey === 'prefix_id') {
                    $transformedValue = $this->transformPrefixId($value);
                }
                // Handle gender_id
                elseif ($lowerKey === 'gender_id') {
                    $transformedValue = $this->transformGenderId($value);
                }
                // Handle service_location_id
                elseif ($lowerKey === 'service_location_id' || $lowerKey === 'location_id') {
                    $transformedValue = $this->transformServiceLocationId($value);
                }
                // Handle service_id
                elseif ($lowerKey === 'service_id') {
                    $transformedValue = $this->transformServiceId($value);
                }
                // Handle service_mode_id
                elseif ($lowerKey === 'service_mode_id') {
                    $transformedValue = $this->transformServiceModeId($value);
                }
                // Handle cc_type (Cost Center Type ID to name)
                elseif ($lowerKey === 'cc_type') {
                    $transformedValue = $this->transformCcTypeId($value);
                }
                // Handle billing_cc
                elseif ($lowerKey === 'billing_cc' || $lowerKey === 'cc_id') {
                    $transformedValue = $this->transformCostCenterId($value);
                }
                // Handle schedule_id
                elseif ($lowerKey === 'schedule_id') {
                    $transformedValue = $this->transformScheduleId($value);
                }
                // Handle ordering_cc_ids (comma-separated)
                elseif ($lowerKey === 'ordering_cc_ids') {
                    $transformedValue = $this->transformCommaSeparatedIds($value, 'costcenter');
                }
                // Handle performing_cc_ids (comma-separated)
                elseif ($lowerKey === 'performing_cc_ids') {
                    $transformedValue = $this->transformCommaSeparatedIds($value, 'costcenter');
                }
                // Handle servicemode_ids (comma-separated)
                elseif ($lowerKey === 'servicemode_ids') {
                    $transformedValue = $this->transformCommaSeparatedIds($value, 'service_mode');
                }
                elseif ($lowerKey === 'icd_id' || $lowerKey === 'complaints') {
                    $transformedValue = $this->transformMedicalCodeIds($value);
                }
                // Handle province_id
                elseif ($lowerKey === 'province_id') {
                    $transformedValue = $this->transformProvinceId($value);
                }
                // Handle type_id
                elseif ($lowerKey === 'type_id') {
                    $transformedValue = $this->transformTypeId($value);
                }
                // Handle icd_type (s=Symptom, p=Procedure, d=Diagnosis)
                elseif ($lowerKey === 'icd_type') {
                    $transformedValue = $this->transformIcdType($value);
                }
                elseif ($lowerKey === 'action') {
                    $transformedValue = $this->transformReqEPI($value);
                }
                // Handle kpi_group id
                elseif ($lowerKey === 'kpi_group') {
                    $transformedValue = $this->transformKpiGroupId($value);
                }
                // Handle kpi_dimension id
                elseif ($lowerKey === 'kpi_dimension') {
                    $transformedValue = $this->transformKpiDimensionId($value);
                }
                // Handle kpi_type id
                elseif ($lowerKey === 'kpi_type') {
                    $transformedValue = $this->transformKpiTypeId($value);
                }
                elseif ($lowerKey === 'kpi_id') {
                    $transformedValue = $this->transformKpi($value);
                }
                elseif ($lowerKey === 'cadre_id') {
                    $transformedValue = $this->transformCadre($value);
                }
                // Handle position_id
                elseif ($lowerKey === 'position_id') {
                    $transformedValue = $this->transformPositionId($value);
                }
                // Handle group_id
                elseif ($lowerKey === 'group_id') {
                    $transformedValue = $this->transformGroupId($value);
                }
                elseif ($lowerKey === 'unit_id') {
                    $transformedValue = $this->transformUnitId($value);
                }
                // Handle consumption_group
                elseif ($lowerKey === 'consumption_group') {
                    $transformedValue = $this->transformConsumptionGroupId($value);
                }
                // Handle consumption_method
                elseif ($lowerKey === 'consumption_method') {
                    $transformedValue = $this->transformConsumptionMethodId($value);
                }
                // Handle cat_id (inventory category)
                elseif ($lowerKey === 'cat_id' || $lowerKey === 'category_id') {
                    $transformedValue = $this->transformCatId($value);
                }
                // Handle sub_catid (inventory subcategory)
                elseif ($lowerKey === 'sub_catid' || $lowerKey === 'sub_category_id') {
                    $transformedValue = $this->transformSubCatId($value);
                }
                // Handle generic_id (inventory generic)
                elseif ($lowerKey === 'generic_id') {
                    $transformedValue = $this->transformGenericId($value);
                }
                // Handle brand_id (inventory brand)
                elseif ($lowerKey === 'brand_id') {
                    $transformedValue = $this->transformBrandId($value);
                }
                // Handle activity_type (inventory transaction activity)
                elseif ($lowerKey === 'activity_type') {
                    $transformedValue = $this->transformActivityType($value);
                }
                // Handle source_location_type (inventory source destination type)
                elseif ($lowerKey === 'source_location_type') {
                    $transformedValue = $this->transformSourceDestinationType($value);
                }
                // Handle destination_location_type (inventory source destination type)
                elseif ($lowerKey === 'destination_location_type') {
                    $transformedValue = $this->transformSourceDestinationType($value);
                }
                // Handle source_action (a=Add, s=Subtract, r=Reversal, n=Not Applicable)
                elseif ($lowerKey === 'source_action') {
                    $transformedValue = $this->transformActionType($value);
                }
                // Handle destination_action (a=Add, s=Subtract, r=Reversal, n=Not Applicable)
                elseif ($lowerKey === 'destination_action') {
                    $transformedValue = $this->transformActionType($value);
                }
                // Handle source_location (comma-separated service location IDs)
                elseif ($lowerKey === 'source_location') {
                    $transformedValue = $this->transformCommaSeparatedIds($value, 'service_location');
                }
                // Handle destination_location (comma-separated service location IDs)
                elseif ($lowerKey === 'destination_location') {
                    $transformedValue = $this->transformCommaSeparatedIds($value, 'service_location');
                }
                // Handle emp_location_check (s=Source, d=Destination, n=Not Applicable)
                elseif ($lowerKey === 'emp_location_check') {
                    $transformedValue = $this->transformEmpLocationType($value);
                }
                // Handle emp_location_source_destination (s=Source, d=Destination, n=Not Applicable)
                elseif ($lowerKey === 'emp_location_source_destination') {
                    $transformedValue = $this->transformEmpLocationType($value);
                }
                // Handle thirdparty_type (v=Vendor, d=Donor)
                elseif ($lowerKey === 'thirdparty_type' || $lowerKey === 'third_party_type') {
                    $transformedValue = $this->transformThirdPartyType($value);
                }
                // Handle thirdpartycategory (c=Corporate, i=Individual)
                elseif ($lowerKey === 'thirdpartycategory' || $lowerKey === 'third_party_category' || $lowerKey === 'thirdparty_category') {
                    $transformedValue = $this->transformThirdPartyCategory($value);
                }
                // Handle vendor_id (third party vendor)
                elseif ($lowerKey === 'vendor_id') {
                    $transformedValue = $this->transformVendorId($value);
                }
                // Handle patient_mandatory (y/n to Yes/No)
                elseif ($lowerKey === 'patient_mandatory' || $lowerKey === 'third_party' || $lowerKey === 'request_mandatory' || $lowerKey === 'transaction_expired_status') {
                    $transformedValue = $this->transformYesNo($value);
                }
                // Handle charge (0/1 to No/Yes)
                elseif ($lowerKey === 'charge' || $lowerKey === 'ordering' || $lowerKey === 'performing' || $lowerKey === 'inventory_status' || $lowerKey === 'mandatory' || $lowerKey === 'job_continue') {
                    $transformedValue = $this->transformBoolean($value);
                }
                // Handle division_id
                elseif ($lowerKey === 'division_id') {
                    $transformedValue = $this->transformDivisionId($value);
                }
                // Handle district_id
                elseif ($lowerKey === 'district_id') {
                    $transformedValue = $this->transformDistrictId($value);
                }
                // Handle boolean flags (0/1 to Yes/No)
                elseif (preg_match('/^(is_|enabled|disabled|active|inactive)/', $lowerKey)) {
                    $transformedValue = $this->transformBoolean($value);
                }
                // Handle site_enabled
                elseif ($lowerKey === 'site_enabled') {
                    $transformedValue = $this->transformBoolean($value);
                }
                elseif ($lowerKey === 'icd_id') {
                    $transformedValue = $this->transformMedicalCodes($value);
                }
                // Handle effective_timestamp
                elseif ($lowerKey === 'effective_timestamp' || $lowerKey === 'service_start_time' || $lowerKey === 'investigation_confirmation_datetime') {
                    if (is_numeric($value) && $value > 0) {
                        $transformedValue = Carbon::createFromTimestamp($value)->format('l d F Y - h:i:s A');
                    } else {
                        $transformedValue = 'Not Set';
                    }
                }
                elseif ($lowerKey === 'start_timestamp' || $lowerKey === 'end_timestamp') {
                    $transformedValue = Carbon::createFromTimestamp($value)->format('h:i A ');
                }

                // Handle email - keep original format
                elseif ($lowerKey === 'email') {
                    $transformedValue = $value;
                }
                // Handle string values with ucwords
                elseif (is_string($value)) {
                    $transformedValue = ucwords(strtolower($value));
                }
                // Recursively check nested arrays or other types
                else {
                    $transformedValue = $this->transformStatusValues($value);
                }
                
                
                $transformed[$key] = $transformedValue;
            }
            return $transformed;
        }
        
        return $data;
    }

    /**
     * Transform status 0/1 to Inactive/Active
     */
    private function transformStatus($value)
    {
        if ($value === 0 || $value === '0') {
            return 'Inactive';
        } elseif ($value === 1 || $value === '1') {
            return 'Active';
        }
        return $value;
    }

    /**
     * Transform boolean 0/1 to No/Yes
     */
    private function transformBoolean($value)
    {
        if ($value === 0 || $value === '0') {
            return 'No';
        } elseif ($value === 1 || $value === '1') {
            return 'Yes';
        }
        return $value;
    }

    /**
     * Transform patient_mandatory y/n to Yes/No
     */
    private function transformYesNo($value)
    {
        if ($value === null || $value === '') {
            return 'N/A';
        }
        
        $value = is_string($value) ? strtolower(trim($value)) : $value;
        
        if ($value === 'y' || $value === 'yes') {
            return 'Yes';
        } elseif ($value === 'n' || $value === 'no') {
            return 'No';
        }
        
        return $value;
    }

    /**
     * Get role name from role_id
     */
    private function transformRoleId($roleId)
    {
        if (!$roleId || $roleId === 0 || $roleId === '0') {
            return 'N/A';
        }
        
        // Try to find role by ID
        $role = DB::table('role')->where('id', $roleId)->first();
        
        if ($role) {
            return ucwords(strtolower($role->role));
        }
        
        // If not found by ID, check if it's already a role name string
        if (is_string($roleId) && !is_numeric($roleId)) {
            return ucwords(strtolower($roleId));
        }
        
        // Fallback: just return the ID without prefix
        return is_numeric($roleId) ? $roleId : ucwords(strtolower($roleId));
    }

    /**
     * Get organization name from org_id
     */
    private function transformOrgId($orgId)
    {
        if (!$orgId || $orgId === 0 || $orgId === '0') {
            return 'N/A';
        }
        
        // Try to find organization by ID
        $org = DB::table('organization')->where('id', $orgId)->first();
        
        if ($org) {
            return ucwords(strtolower($org->organization));
        }
        
        // If not found by ID, check if it's already an organization name string
        if (is_string($orgId) && !is_numeric($orgId)) {
            return ucwords(strtolower($orgId));
        }
        
        // Fallback: just return the ID without prefix
        return is_numeric($orgId) ? $orgId : ucwords(strtolower($orgId));
    }

    /**
     * Get employee name from emp_id
     */
    private function transformEmpId($empId)
    {
        if (!$empId || $empId === 0 || $empId === '0') {
            return 'N/A';
        }
        
        // Try to find employee by ID
        $employee = DB::table('employee')->where('id', $empId)->first();
        
        if ($employee) {
            return ucwords(strtolower($employee->name));
        }
        
        // If not found by ID, check if it's already an employee name string
        if (is_string($empId) && !is_numeric($empId)) {
            return ucwords(strtolower($empId));
        }
        
        // Fallback: just return the ID without prefix
        return is_numeric($empId) ? $empId : ucwords(strtolower($empId));
    }

    /**
     * Get province name from province_id
     */
    private function transformProvinceId($provinceId)
    {
        if (!$provinceId || $provinceId === 0 || $provinceId === '0') {
            return 'N/A';
        }
        
        // Try to find province by ID
        $province = DB::table('province')->where('id', $provinceId)->first();
        
        if ($province) {
            return ucwords(strtolower($province->name));
        }
        
        // If not found by ID, check if it's already a province name string
        if (is_string($provinceId) && !is_numeric($provinceId)) {
            return ucwords(strtolower($provinceId));
        }
        
        // Fallback: just return the ID without prefix
        return is_numeric($provinceId) ? $provinceId : ucwords(strtolower($provinceId));
    }

    /**
     * Get division name from division_id
     */
    private function transformDivisionId($divisionId)
    {
        if (!$divisionId || $divisionId === 0 || $divisionId === '0') {
            return 'N/A';
        }
        
        // Try to find division by ID
        $division = DB::table('division')->where('id', $divisionId)->first();
        
        if ($division) {
            return ucwords(strtolower($division->name));
        }
        
        // If not found by ID, check if it's already a division name string
        if (is_string($divisionId) && !is_numeric($divisionId)) {
            return ucwords(strtolower($divisionId));
        }
        
        // Fallback: just return the ID without prefix
        return is_numeric($divisionId) ? $divisionId : ucwords(strtolower($divisionId));
    }

    /**
     * Get district name from district_id
     */
    private function transformDistrictId($districtId)
    {
        if (!$districtId || $districtId === 0 || $districtId === '0') {
            return 'N/A';
        }
        
        // Try to find district by ID
        $district = DB::table('district')->where('id', $districtId)->first();
        
        if ($district) {
            return ucwords(strtolower($district->name));
        }
        
        // If not found by ID, check if it's already a district name string
        if (is_string($districtId) && !is_numeric($districtId)) {
            return ucwords(strtolower($districtId));
        }
        
        // Fallback: just return the ID without prefix
        return is_numeric($districtId) ? $districtId : ucwords(strtolower($districtId));
    }

  

    /**
     * Get service type or inventory type name from type_id
     */
    private function transformTypeId($typeId)
    {
        if (!$typeId || $typeId === 0 || $typeId === '0') {
            return 'N/A';
        }
        
        // First try to find inventory type by ID
        $inventoryType = DB::table('inventory_type')->where('id', $typeId)->first();
        
        if ($inventoryType && isset($inventoryType->name)) {
            return ucwords(strtolower($inventoryType->name));
        }
        
        // If not found, try to find service type by ID
        $serviceType = DB::table('service_type')->where('id', $typeId)->first();
        
        if ($serviceType) {
            return ucwords(strtolower($serviceType->name));
        }
        
        // If not found by ID, check if it's already a type name string
        if (is_string($typeId) && !is_numeric($typeId)) {
            return ucwords(strtolower($typeId));
        }
        
        // Fallback: just return the ID without prefix
        return is_numeric($typeId) ? $typeId : ucwords(strtolower($typeId));
    }

    /**
     * Transform ICD type short code to readable text
     * s => Symptom, p => Procedure, d => Diagnosis
     */
    private function transformIcdType($value)
    {
        if ($value === null || $value === '') {
            return 'N/A';
        }
        $v = is_string($value) ? strtolower(trim($value)) : $value;
        if ($v === 's') {
            return 'Symptom';
        }
        if ($v === 'p') {
            return 'Procedure';
        }
        if ($v === 'd') {
            return 'Diagnosis';
        }
        return is_string($value) ? ucwords(strtolower($value)) : $value;
    }
    
     private function transformReqEPI($value)
    {
        if ($value === null || $value === '') {
            return 'N/A';
        }
        $v = is_string($value) ? strtolower(trim($value)) : $value;
        if ($v === 'e') {
            return 'Encounter';
        }
        if ($v === 'p') {
            return 'Procedure';
        }
        if ($v === 'i') {
            return 'Investigation';
        }
        return is_string($value) ? ucwords(strtolower($value)) : $value;
    }

    /**
     * Get service group name from group_id
     */
    private function transformGroupId($groupId)
    {
        if (!$groupId || $groupId === 0 || $groupId === '0') {
            return 'N/A';
        }
        
        // Try to find service group by ID
        $serviceGroup = DB::table('service_group')->where('id', $groupId)->first();
        
        if ($serviceGroup) {
            return ucwords(strtolower($serviceGroup->name));
        }
        
        // If not found by ID, check if it's already a service group name string
        if (is_string($groupId) && !is_numeric($groupId)) {
            return ucwords(strtolower($groupId));
        }
        
        // Fallback: just return the ID without prefix
        return is_numeric($groupId) ? $groupId : ucwords(strtolower($groupId));
    }

    /**
     * Get service unit name from unit_id
     */
    private function transformUnitId($unitId)
    {
        if (!$unitId || $unitId === 0 || $unitId === '0') {
            return 'N/A';
        }
        
        // Try to find service unit by ID
        $serviceUnit = DB::table('service_unit')->where('id', $unitId)->first();
        
        if ($serviceUnit) {
            return ucwords(strtolower($serviceUnit->name));
        }
        
        // If not found by ID, check if it's already a service unit name string
        if (is_string($unitId) && !is_numeric($unitId)) {
            return ucwords(strtolower($unitId));
        }
        
        // Fallback: just return the ID without prefix
        return is_numeric($unitId) ? $unitId : ucwords(strtolower($unitId));
    }

    /**
     * Get consumption group name from consumption_group ID
     */
    private function transformConsumptionGroupId($consumptionGroupId)
    {
        if (!$consumptionGroupId || $consumptionGroupId === 0 || $consumptionGroupId === '0') {
            return 'N/A';
        }
        
        // Try to find consumption group by ID
        $consumptionGroup = DB::table('consumption_group')->where('id', $consumptionGroupId)->first();
        
        if ($consumptionGroup && isset($consumptionGroup->description)) {
            return ucwords(strtolower($consumptionGroup->description));
        }
        
        // If not found by ID, check if it's already a consumption group name string
        if (is_string($consumptionGroupId) && !is_numeric($consumptionGroupId)) {
            return ucwords(strtolower($consumptionGroupId));
        }
        
        // Fallback: just return the ID without prefix
        return is_numeric($consumptionGroupId) ? $consumptionGroupId : ucwords(strtolower($consumptionGroupId));
    }

    /**
     * Get consumption method name from consumption_method ID
     */
    private function transformConsumptionMethodId($consumptionMethodId)
    {
        if (!$consumptionMethodId || $consumptionMethodId === 0 || $consumptionMethodId === '0') {
            return 'N/A';
        }
        
        // Try to find consumption method by ID
        $consumptionMethod = DB::table('consumption_method')->where('id', $consumptionMethodId)->first();
        
        if ($consumptionMethod && isset($consumptionMethod->description)) {
            return ucwords(strtolower($consumptionMethod->description));
        }
        
        // If not found by ID, check if it's already a consumption method name string
        if (is_string($consumptionMethodId) && !is_numeric($consumptionMethodId)) {
            return ucwords(strtolower($consumptionMethodId));
        }
        
        // Fallback: just return the ID without prefix
        return is_numeric($consumptionMethodId) ? $consumptionMethodId : ucwords(strtolower($consumptionMethodId));
    }

    /**
     * Get inventory category name from cat_id
     */
    private function transformCatId($catId)
    {
        if (!$catId || $catId === 0 || $catId === '0') {
            return 'N/A';
        }
        
        // Try to find inventory category by ID
        $category = DB::table('inventory_category')->where('id', $catId)->first();
        
        if ($category && isset($category->name)) {
            return ucwords(strtolower($category->name));
        }
        
        // If not found by ID, check if it's already a category name string
        if (is_string($catId) && !is_numeric($catId)) {
            return ucwords(strtolower($catId));
        }
        
        // Fallback: just return the ID without prefix
        return is_numeric($catId) ? $catId : ucwords(strtolower($catId));
    }

    /**
     * Get inventory subcategory name from sub_catid
     */
    private function transformSubCatId($subCatId)
    {
        if (!$subCatId || $subCatId === 0 || $subCatId === '0') {
            return 'N/A';
        }
        
        // Try to find inventory subcategory by ID
        $subCategory = DB::table('inventory_subcategory')->where('id', $subCatId)->first();
        
        if ($subCategory && isset($subCategory->name)) {
            return ucwords(strtolower($subCategory->name));
        }
        
        // If not found by ID, check if it's already a subcategory name string
        if (is_string($subCatId) && !is_numeric($subCatId)) {
            return ucwords(strtolower($subCatId));
        }
        
        // Fallback: just return the ID without prefix
        return is_numeric($subCatId) ? $subCatId : ucwords(strtolower($subCatId));
    }

    /**
     * Get inventory generic name from generic_id
     */
    private function transformGenericId($genericId)
    {
        if (!$genericId || $genericId === 0 || $genericId === '0') {
            return 'N/A';
        }
        
        // Try to find inventory generic by ID
        $generic = DB::table('inventory_generic')->where('id', $genericId)->first();
        
        if ($generic && isset($generic->name)) {
            return ucwords(strtolower($generic->name));
        }
        
        // If not found by ID, check if it's already a generic name string
        if (is_string($genericId) && !is_numeric($genericId)) {
            return ucwords(strtolower($genericId));
        }
        
        // Fallback: just return the ID without prefix
        return is_numeric($genericId) ? $genericId : ucwords(strtolower($genericId));
    }

    /**
     * Get inventory brand name from brand_id
     */
    private function transformBrandId($brandId)
    {
        if (!$brandId || $brandId === 0 || $brandId === '0') {
            return 'N/A';
        }
        
        // Try to find inventory brand by ID
        $brand = DB::table('inventory_brand')->where('id', $brandId)->first();
        
        if ($brand && isset($brand->name)) {
            return ucwords(strtolower($brand->name));
        }
        
        // If not found by ID, check if it's already a brand name string
        if (is_string($brandId) && !is_numeric($brandId)) {
            return ucwords(strtolower($brandId));
        }
        
        // Fallback: just return the ID without prefix
        return is_numeric($brandId) ? $brandId : ucwords(strtolower($brandId));
    }

    /**
     * Get inventory transaction activity name from activity_type
     */
    private function transformActivityType($activityTypeId)
    {
        if (!$activityTypeId || $activityTypeId === 0 || $activityTypeId === '0') {
            return 'N/A';
        }
        
        // Try to find inventory transaction activity by ID
        $activity = DB::table('inventory_transaction_activity')->where('id', $activityTypeId)->first();
        
        if ($activity && isset($activity->name)) {
            return ucwords(strtolower($activity->name));
        }
        
        // If not found by ID, check if it's already an activity name string
        if (is_string($activityTypeId) && !is_numeric($activityTypeId)) {
            return ucwords(strtolower($activityTypeId));
        }
        
        // Fallback: just return the ID without prefix
        return is_numeric($activityTypeId) ? $activityTypeId : ucwords(strtolower($activityTypeId));
    }

    /**
     * Get inventory source destination type name from source/destination location type
     */
    private function transformSourceDestinationType($typeId)
    {
        if (!$typeId || $typeId === 0 || $typeId === '0') {
            return 'N/A';
        }
        
        // Try to find inventory source destination type by ID
        $type = DB::table('inventory_source_destination_type')->where('id', $typeId)->first();
        
        if ($type && isset($type->name)) {
            return ucwords(strtolower($type->name));
        }
        
        // If not found by ID, check if it's already a type name string
        if (is_string($typeId) && !is_numeric($typeId)) {
            return ucwords(strtolower($typeId));
        }
        
        // Fallback: just return the ID without prefix
        return is_numeric($typeId) ? $typeId : ucwords(strtolower($typeId));
    }

    /**
     * Transform action type codes to readable text
     * a => Add, s => Subtract, r => Reversal, n => Not Applicable
     */
    private function transformActionType($value)
    {
        if ($value === null || $value === '') {
            return 'N/A';
        }
        
        $v = is_string($value) ? strtolower(trim($value)) : $value;
        
        $actionMap = [
            'a' => 'Add',
            's' => 'Subtract',
            'r' => 'Reversal',
            'n' => 'Not Applicable'
        ];
        
        return $actionMap[$v] ?? ucwords(strtolower($value));
    }

    /**
     * Transform employee location type codes to readable text
     * s => Source, d => Destination, n => Not Applicable
     */
    private function transformEmpLocationType($value)
    {
        if ($value === null || $value === '') {
            return 'N/A';
        }
        
        $v = is_string($value) ? strtolower(trim($value)) : $value;
        
        $locationMap = [
            's' => 'Source',
            'd' => 'Destination',
            'n' => 'Not Applicable'
        ];
        
        return $locationMap[$v] ?? ucwords(strtolower($value));
    }

    /**
     * Transform third party type codes to readable text
     * v => Vendor, d => Donor
     */
    private function transformThirdPartyType($value)
    {
        if ($value === null || $value === '') {
            return 'N/A';
        }
        
        $v = is_string($value) ? strtolower(trim($value)) : $value;
        
        $typeMap = [
            'v' => 'Vendor',
            'd' => 'Donor'
        ];
        
        return $typeMap[$v] ?? ucwords(strtolower($value));
    }

    /**
     * Transform third party category codes to readable text
     * c => Corporate, i => Individual
     */
    private function transformThirdPartyCategory($value)
    {
        if ($value === null || $value === '') {
            return 'N/A';
        }
        
        $v = is_string($value) ? strtolower(trim($value)) : $value;
        
        $categoryMap = [
            'c' => 'Corporate',
            'i' => 'Individual'
        ];
        
        return $categoryMap[$v] ?? ucwords(strtolower($value));
    }

    /**
     * Get vendor name from vendor_id (third_party table)
     */
    private function transformVendorId($vendorId)
    {
        if (!$vendorId || $vendorId === 0 || $vendorId === '0') {
            return 'N/A';
        }
        
        // Try to find vendor in third_party table by ID
        $vendor = DB::table('third_party')->where('id', $vendorId)->first();
        
        if ($vendor) {
            // Return corporate name if exists, otherwise person name
            if (!empty($vendor->corporate_name)) {
                return ucwords(strtolower($vendor->corporate_name));
            } elseif (!empty($vendor->person_name)) {
                // Get prefix if available
                $prefix = '';
                if (!empty($vendor->prefix_id)) {
                    $prefixRecord = DB::table('prefix')->where('id', $vendor->prefix_id)->first();
                    if ($prefixRecord && !empty($prefixRecord->name)) {
                        $prefix = ucwords(strtolower($prefixRecord->name)) . ' ';
                    }
                }
                return $prefix . ucwords(strtolower($vendor->person_name));
            }
        }
        
        // If not found by ID, check if it's already a vendor name string
        if (is_string($vendorId) && !is_numeric($vendorId)) {
            return ucwords(strtolower($vendorId));
        }
        
        // Fallback: just return the ID without prefix
        return is_numeric($vendorId) ? $vendorId : ucwords(strtolower($vendorId));
    }

    /**
     * Get site name from site_id
     */
    private function transformSiteId($siteId)
    {
        if (!$siteId || $siteId === 0 || $siteId === '0') {
            return 'N/A';
        }
        
        // Try to find site by ID
        $site = DB::table('org_site')->where('id', $siteId)->first();
        
        if ($site) {
            return ucwords(strtolower($site->name));
        }
        
        // If not found by ID, check if it's already a site name string
        if (is_string($siteId) && !is_numeric($siteId)) {
            return ucwords(strtolower($siteId));
        }
        
        // Fallback: just return the ID without prefix
        return is_numeric($siteId) ? $siteId : ucwords(strtolower($siteId));
    }

    /**
     * Get service location name from service_location_id
     */
    private function transformServiceLocationId($serviceLocationId)
    {
        if (!$serviceLocationId || $serviceLocationId === 0 || $serviceLocationId === '0') {
            return 'N/A';
        }
        
        // Try to find service location by ID
        $serviceLocation = DB::table('service_location')->where('id', $serviceLocationId)->first();
        
        if ($serviceLocation) {
            return ucwords(strtolower($serviceLocation->name));
        }
        
        // If not found by ID, check if it's already a service location name string
        if (is_string($serviceLocationId) && !is_numeric($serviceLocationId)) {
            return ucwords(strtolower($serviceLocationId));
        }
        
        // Fallback: just return the ID without prefix
        return is_numeric($serviceLocationId) ? $serviceLocationId : ucwords(strtolower($serviceLocationId));
    }

    /**
     * Get service name from service_id
     */
    private function transformServiceId($serviceId)
    {
        if (!$serviceId || $serviceId === 0 || $serviceId === '0') {
            return 'N/A';
        }

        // Handle comma-separated IDs
        if (is_string($serviceId) && strpos($serviceId, ',') !== false) {
            $ids = explode(',', $serviceId);
            $names = [];

            foreach ($ids as $id) {
                $id = trim($id);
                if ($id === '' || $id === '0') {
                    continue;
                }

                $record = DB::table('services')->where('id', $id)->first();
                if ($record) {
                    $names[] = ucwords(strtolower($record->name));
                } else {
                    $names[] = $id;
                }
            }

            return implode(', ', $names);
        }

        // If not found by ID, check if it's already a service name string
        if (is_string($serviceId) && !is_numeric($serviceId)) {
            return ucwords(strtolower($serviceId));
        }

        // Single ID
        $service = DB::table('services')->where('id', $serviceId)->first();
        if ($service) {
            return ucwords(strtolower($service->name));
        }

        // Fallback
        return $serviceId;
    }

    /**
     * Get service mode name from service_mode_id
     */
    private function transformServiceModeId($serviceModeId)
    {
        if (!$serviceModeId || $serviceModeId === 0 || $serviceModeId === '0') {
            return 'N/A';
        }
        
        // Try to find service mode by ID
        $serviceMode = DB::table('service_mode')->where('id', $serviceModeId)->first();
        
        if ($serviceMode) {
            return ucwords(strtolower($serviceMode->name));
        }
        
        // If not found by ID, check if it's already a service mode name string
        if (is_string($serviceModeId) && !is_numeric($serviceModeId)) {
            return ucwords(strtolower($serviceModeId));
        }
        
        // Fallback: just return the ID without prefix
        return is_numeric($serviceModeId) ? $serviceModeId : ucwords(strtolower($serviceModeId));
    }

    /**
     * Get KPI group name from kpi_group id
     */
    private function transformKpiGroupId($groupId)
    {
        if (!$groupId || $groupId === 0 || $groupId === '0') {
            return 'N/A';
        }
        
        $group = DB::table('kpi_group')->where('id', $groupId)->first();
        if ($group && isset($group->name)) {
            return ucwords(strtolower($group->name));
        }
        
        if (is_string($groupId) && !is_numeric($groupId)) {
            return ucwords(strtolower($groupId));
        }
        
        return is_numeric($groupId) ? $groupId : ucwords(strtolower($groupId));
    }

    private function transformPrefixId($id)
    {
        if (!$id || $id === 0 || $id === '0') {
            return 'N/A';
        }
        $prefix = DB::table('prefix')->where('id', $id)->first();
        if ($prefix && isset($prefix->name)) {
            return ucwords(strtolower($prefix->name));
        }
        if (is_string($id) && !is_numeric($id)) {
            return ucwords(strtolower($id));
        }
        return is_numeric($id) ? $id : ucwords(strtolower($id));
    }

    private function transformGenderId($id)
    {
        if (!$id || $id === 0 || $id === '0') {
            return 'N/A';
        }
        $gender = DB::table('gender')->where('id', $id)->first();
        if ($gender && isset($gender->name)) {
            return ucwords(strtolower($gender->name));
        }
        if (is_string($id) && !is_numeric($id)) {
            return ucwords(strtolower($id));
        }
        return is_numeric($id) ? $id : ucwords(strtolower($id));
    }

    private function transformPositionId($id)
    {
        if (!$id || $id === 0 || $id === '0') {
            return 'N/A';
        }
        $position = DB::table('emp_position')->where('id', $id)->first();
        if ($position && isset($position->name)) {
            return ucwords(strtolower($position->name));
        }
        if (is_string($id) && !is_numeric($id)) {
            return ucwords(strtolower($id));
        }
        return is_numeric($id) ? $id : ucwords(strtolower($id));
    }

    private function transformCadre($id)
    {
        if (!$id || $id === 0 || $id === '0') {
            return 'N/A';
        }
        
        $Cadre = DB::table('emp_cadre')->where('id', $id)->first();
        if ($Cadre && isset($Cadre->name)) {
            return ucwords(strtolower($Cadre->name));
        }
        
        if (is_string($id) && !is_numeric($id)) {
            return ucwords(strtolower($id));
        }
        
        return is_numeric($id) ? $id : ucwords(strtolower($id));
    }

    /**
     * Get KPI dimension name from kpi_dimension id
     */
    private function transformKpiDimensionId($dimensionId)
    {
        if (!$dimensionId || $dimensionId === 0 || $dimensionId === '0') {
            return 'N/A';
        }
        
        $dimension = DB::table('kpi_dimension')->where('id', $dimensionId)->first();
        if ($dimension && isset($dimension->name)) {
            return ucwords(strtolower($dimension->name));
        }
        
        if (is_string($dimensionId) && !is_numeric($dimensionId)) {
            return ucwords(strtolower($dimensionId));
        }
        
        return is_numeric($dimensionId) ? $dimensionId : ucwords(strtolower($dimensionId));
    }

    /**
     * Get KPI type name from kpi_type id
     */
    private function transformKpiTypeId($typeId)
    {
        if (!$typeId || $typeId === 0 || $typeId === '0') {
            return 'N/A';
        }
        
        $type = DB::table('kpi_type')->where('id', $typeId)->first();
        if ($type && isset($type->name)) {
            return ucwords(strtolower($type->name));
        }
        
        if (is_string($typeId) && !is_numeric($typeId)) {
            return ucwords(strtolower($typeId));
        }
        
        return is_numeric($typeId) ? $typeId : ucwords(strtolower($typeId));
    }

    private function transformKpi($id)
    {
        if (!$id || $id === 0 || $id === '0') {
            return 'N/A';
        }
        
        $kpi = DB::table('kpi')->where('id', $id)->first();
        if ($kpi && isset($kpi->name)) {
            return ucwords(strtolower($kpi->name));
        }
        
        if (is_string($id) && !is_numeric($id)) {
            return ucwords(strtolower($id));
        }
        
        return is_numeric($id) ? $id : ucwords(strtolower($id));
    }

    /**
     * Get cost center type name from cc_type
     */
    private function transformCcTypeId($ccTypeId)
    {
        if (!$ccTypeId || $ccTypeId === 0 || $ccTypeId === '0') {
            return 'N/A';
        }
        
        // Try to find cost center type by ID
        $ccType = DB::table('cc_type')->where('id', $ccTypeId)->first();
        
        if ($ccType && isset($ccType->type)) {
            return ucwords(strtolower($ccType->type));
        }
        
        // If not found by ID, check if it's already a type name string
        if (is_string($ccTypeId) && !is_numeric($ccTypeId)) {
            return ucwords(strtolower($ccTypeId));
        }
        
        // Fallback: just return the ID without prefix
        return is_numeric($ccTypeId) ? $ccTypeId : ucwords(strtolower($ccTypeId));
    }



    /**
     * Get cost center name from billing_cc
     */
    private function transformCostCenterId($costCenterId)
    {
        if (!$costCenterId || $costCenterId === 0 || $costCenterId === '0') {
            return 'N/A';
        }
        
        // Try to find cost center by ID
        $costCenter = DB::table('costcenter')->where('id', $costCenterId)->first();
        
        if ($costCenter) {
            return ucwords(strtolower($costCenter->name));
        }
        
        // If not found by ID, check if it's already a cost center name string
        if (is_string($costCenterId) && !is_numeric($costCenterId)) {
            return ucwords(strtolower($costCenterId));
        }
        
        // Fallback: just return the ID without prefix
        return is_numeric($costCenterId) ? $costCenterId : ucwords(strtolower($costCenterId));
    }

    /**
     * Get schedule name from schedule_id
     */
    private function transformScheduleId($scheduleId)
    {
        if (!$scheduleId || $scheduleId === 0 || $scheduleId === '0') {
            return 'N/A';
        }
        
        // Try to find service location scheduling by ID
        $schedule = DB::table('service_location_scheduling')->where('id', $scheduleId)->first();
        
        if ($schedule) {
            return ucwords(strtolower($schedule->name));
        }
        
        // If not found by ID, check if it's already a schedule name string
        if (is_string($scheduleId) && !is_numeric($scheduleId)) {
            return ucwords(strtolower($scheduleId));
        }
        
        // Fallback: just return the ID without prefix
        return is_numeric($scheduleId) ? $scheduleId : ucwords(strtolower($scheduleId));
    }

    /**
     * Transform comma-separated IDs to names
     */
    private function transformCommaSeparatedIds($value, $tableName)
    {
        if (!$value || $value === '' || $value === 0 || $value === '0') {
            return 'N/A';
        }
        
        // If it's already a string with names, return as is
        if (is_string($value) && !is_numeric($value) && strpos($value, ',') === false) {
            return ucwords(strtolower($value));
        }
        
        // Handle comma-separated IDs
        if (is_string($value) && strpos($value, ',') !== false) {
            $ids = explode(',', $value);
            $names = [];
            
            foreach ($ids as $id) {
                $id = trim($id);
                if ($id === '' || $id === '0') {
                    continue;
                }
                
                $record = DB::table($tableName)->where('id', $id)->first();
                if ($record) {
                    $names[] = ucwords(strtolower($record->name));
                } else {
                    $names[] = $id;
                }
            }
            
            return implode(', ', $names);
        }
        
        // Single ID
        $record = DB::table($tableName)->where('id', $value)->first();
        if ($record) {
            return ucwords(strtolower($record->name));
        }
        
        // Fallback
        return $value;
    }

    private function transformMedicalCodeIds($value)
    {
        if (!$value || $value === '' || $value === 0 || $value === '0') {
            return 'N/A';
        }
        $tableName = 'icd_code';
        
        // If it's already a single non-numeric string without commas, return as-is
        if (is_string($value) && !is_numeric($value) && strpos($value, ',') === false) {
            return $value;
        }
        
        // Handle comma-separated IDs
        if (is_string($value) && strpos($value, ',') !== false) {
            $ids = explode(',', $value);
            $lines = [];
            
            foreach ($ids as $id) {
                $id = trim($id);
                if ($id === '' || $id === '0') {
                    continue;
                }
                
                $record = DB::table($tableName)->where('id', $id)->first();
                if ($record) {
                    $code = isset($record->code) ? $record->code : null;
                    $description = isset($record->description) ? ucwords(strtolower($record->description)) : null;
                    if ($code && $description) {
                        $lines[] = $code . ' - ' . $description;
                    } elseif ($code) {
                        $lines[] = $code;
                    } elseif ($description) {
                        $lines[] = $description;
                    } else {
                        $lines[] = $id;
                    }
                } else {
                    $lines[] = $id;
                }
            }
            
            return implode("\n", $lines);
        }
        
        // Single ID
        $record = DB::table($tableName)->where('id', $value)->first();
        if ($record) {
            $code = isset($record->code) ? $record->code : null;
            $description = isset($record->description) ? ucwords(strtolower($record->description)) : null;
            if ($code && $description) {
                return $code . ' - ' . $description;
            } elseif ($code) {
                return $code;
            } elseif ($description) {
                return $description;
            }
        }
        
        // Fallback
        return $value;
    }

}
