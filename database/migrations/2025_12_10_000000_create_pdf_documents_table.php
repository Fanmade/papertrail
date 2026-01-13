<?php

use Fanmade\Papertrail\Types\PdfStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'pdf_documents',
            static function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name');
                $table->string('path');
                $table->string('mime');
                $table->integer('pages');
                $table->integer('size');
                $table->string('status', 16)->nullable();
                $table->timestamps();

                $table->index('path');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('pdf_documents');
    }
};
