<?php

namespace Tests\Feature;

use Tests\TestCase;

class LabelControllerTest extends TestCase
{
    public function test_create_page_is_accessible(): void
    {
        $response = $this->get(route('labels.create'));

        $response->assertOk();
        $response->assertSee('Generator etichete (demo)');
    }

    public function test_store_returns_pdf_download_for_valid_selection(): void
    {
        $response = $this->post(route('labels.store'), [
            'selected_products' => json_encode([
                ['id' => 1, 'quantity' => 2],
            ]),
        ]);

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $response->assertHeader('content-disposition', 'attachment; filename=etichete-produse.pdf');
    }
}
