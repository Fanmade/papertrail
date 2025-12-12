<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pdf_pages', static function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('document_id');
            $table->unsignedInteger('page_number');
            // sizes in PostScript points (1/72 inch)
            $table->unsignedInteger('width_pt')->nullable();
            $table->unsignedInteger('height_pt')->nullable();
            // rendered pixel size at given DPI
            $table->unsignedInteger('width_px')->nullable();
            $table->unsignedInteger('height_px')->nullable();
            $table->unsignedInteger('dpi')->nullable();
            $table->string('image_path')->nullable(); // disk-relative path on configured disk
            $table->timestamps();

            $table->foreign('document_id')->references('id')->on('pdf_documents')->cascadeOnDelete();
            $table->unique(['document_id', 'page_number']);
            $table->index('image_path');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pdf_pages');
    }
};
