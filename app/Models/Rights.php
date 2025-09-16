<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rights extends Model
{
    use HasFactory;
    protected $table = 'rights';
    public $timestamps = false;
    protected $fillable = ['role_id', 'user_setup', 'user_roles',
    'province', 'divisions','districts', 'service_modes', 'service_types','service_units',
    'service_groups','service_code_directory_setup','cost_center_types','cost_center_setup',
    'kpi_group','kpi_dimension','kpi_types','kpi_setup','organization_setup',
    'site_setup','referral_site','cost_center_activation','service_activation','kpi_activation','procedure_coding','service_location_activation',
    'gender_setup','prefix_setup','employee_status_setup','employee_working_status_setup',
    'qualification_level_setup', 'cadre_setup','position_setup',
    'employee_setup','employee_qualification_setup','employee_documents','employee_medical_license_setup',
    'employee_salary_setup','employee_cost_center_allocation',
    'employee_services_allocation','employee_inventory_location_allocation','service_location_setup',
    'service_requisition_setup','service_location_scheduling',
    'patient_registration','services_booking_for_patients',
    'patient_arrival_and_departure','outsourced_services','patient_welfare',
    'medical_coding','vital_signs','encounters_and_procedures','investigation_tracking',
    'item_category','item_sub_category','item_type',
    'item_generic_setup','item_brand_setup','consumption_group','consumption_method','stock_monitoring',
    'third_party_registration','inventory_source_destination_type','inventory_transaction_activity',
    'transaction_types','purchase_order',
    'work_order','external_transaction','issue_and_dispense','consumption','inventory_return',
    'material_transfer','reversal_of_transactions',
    'medication_routes','medication_frequency','requisition_for_material_consumption',
    'requisition_for_material_transfer','chart_of_accounts_strategy','chart_of_accounts_strategy_setup',
    'transaction_sources_or_destinations',
    'financial_ledger_types','payroll_additions_setup','payroll_deduction_setup',
    'finance_transaction_types','finance_receiving','finance_payment','item_rates','service_rates',
    'payroll','taxation',
    'ledger','msd_comprehensive_report','modules','user_id', 'logid','last_updated','timestamp'];
}
