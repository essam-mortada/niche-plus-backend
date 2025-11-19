<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('concierge', function (Blueprint $table) {
            $table->string('contact_name')->nullable()->after('message');
            $table->string('contact_email')->nullable()->after('contact_name');
            $table->string('contact_phone')->nullable()->after('contact_email');
            $table->string('company_name')->nullable()->after('contact_phone');
            $table->string('preferred_date')->nullable()->after('company_name');
            $table->string('preferred_time')->nullable()->after('preferred_date');
            $table->string('location')->nullable()->after('preferred_time');
            $table->integer('number_of_people')->nullable()->after('location');
            $table->decimal('budget', 10, 2)->nullable()->after('number_of_people');
            $table->text('special_requirements')->nullable()->after('budget');
            $table->text('admin_notes')->nullable()->after('status');
        });
    }

    public function down()
    {
        Schema::table('concierge', function (Blueprint $table) {
            $table->dropColumn([
                'contact_name',
                'contact_email',
                'contact_phone',
                'company_name',
                'preferred_date',
                'preferred_time',
                'location',
                'number_of_people',
                'budget',
                'special_requirements',
                'admin_notes'
            ]);
        });
    }
};
