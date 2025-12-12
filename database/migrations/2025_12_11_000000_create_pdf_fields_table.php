<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'pdf_fields',
            static function (Blueprint $table) {
                $table->id();
                $table->foreignUuid('document_id')->references('id')->on('pdf_documents')->constrained()->cascadeOnDelete()->index();
                $table->string('name');
                $table->string('value')->nullable();
                $table->string('type')->nullable();
                $table->integer('page_number');

                $table->float('x');
                $table->float('y');
                $table->float('width');
                $table->float('height');

                $table->string('assigned_placeholder')->nullable();
                $table->timestamps();

                $table->index(['document_id', 'page_number']);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('pdf_fields');
    }
};
