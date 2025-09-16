<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDocuments extends Model
{
    use HasFactory;
    protected $table = 'emp_documents';
    public $timestamps = false;
    protected $fillable = ['document_desc','org_id','site_id','emp_id', 'documents', 
    'status', 'user_id', 'logid',
    'effective_timestamp', 'last_updated','timestamp'];
}
