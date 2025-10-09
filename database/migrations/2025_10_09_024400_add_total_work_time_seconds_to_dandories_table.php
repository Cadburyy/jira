<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up()
{
    Schema::table('dandories', function (Blueprint $table) {
        $table->unsignedBigInteger('total_work_time_seconds')->default(0)->after('check_out');
    });
}

public function down()
{
    Schema::table('dandories', function (Blueprint $table) {
        $table->dropColumn('total_work_time_seconds');
    });
}
};
