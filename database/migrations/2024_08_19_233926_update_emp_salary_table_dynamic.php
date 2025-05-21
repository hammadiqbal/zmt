<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateEmpSalaryTableDynamic extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Fetch payroll additions and deductions
        $payrollAdditions = DB::table('finance_payroll_addition')->where('status', 1)->get();
        $payrollDeductions = DB::table('finance_payroll_deductions')->where('status', 1)->get();

        // Start a transaction to ensure atomicity
        DB::beginTransaction();

        try {
            // Add new columns after the second column using raw SQL
            foreach ($payrollAdditions as $addition) {
                $columnName = str_replace(' ', '_', strtolower($addition->name));
                if (!Schema::hasColumn('emp_salary', $columnName)) {
                    DB::statement("ALTER TABLE emp_salary ADD `$columnName` DECIMAL(10, 2) NULL AFTER `emp_id`");
                }
            }

            foreach ($payrollDeductions as $deduction) {
                $columnName = str_replace(' ', '_', strtolower($deduction->name));
                if (!Schema::hasColumn('emp_salary', $columnName)) {
                    DB::statement("ALTER TABLE emp_salary ADD `$columnName` DECIMAL(10, 2) NULL AFTER `emp_id`");
                }
            }

            // Commit the transaction
            DB::commit();
        } catch (\Exception $e) {
            // Rollback the transaction if anything goes wrong
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Start a transaction to ensure atomicity
        DB::beginTransaction();

        try {
            // Get the columns to drop
            $payrollAdditions = DB::table('finance_payroll_addition')->where('status', 1)->get();
            $payrollDeductions = DB::table('finance_payroll_deductions')->where('status', 1)->get();

            $columnsToDrop = [];

            // Collect columns from additions
            foreach ($payrollAdditions as $addition) {
                $columnName = str_replace(' ', '_', strtolower($addition->name));
                $columnsToDrop[] = $columnName;
            }

            // Collect columns from deductions
            foreach ($payrollDeductions as $deduction) {
                $columnName = str_replace(' ', '_', strtolower($deduction->name));
                $columnsToDrop[] = $columnName;
            }

            // Drop the columns using raw SQL
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('emp_salary', $column)) {
                    DB::statement("ALTER TABLE emp_salary DROP COLUMN `$column`");
                }
            }

            // Commit the transaction
            DB::commit();
        } catch (\Exception $e) {
            // Rollback the transaction if anything goes wrong
            DB::rollBack();
            throw $e;
        }
    }
}
