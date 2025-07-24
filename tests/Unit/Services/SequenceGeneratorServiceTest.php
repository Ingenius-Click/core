<?php

namespace Ingenius\Core\Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Ingenius\Core\Models\Sequence;
use Ingenius\Core\Services\SequenceGeneratorService;
use Tests\TestCase;

class SequenceGeneratorServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SequenceGeneratorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SequenceGeneratorService();
    }

    /** @test */
    public function it_can_generate_a_sequence_number()
    {
        $number = $this->service->generateNumber('test');

        $this->assertEquals('1', $number);

        // Check that the sequence was created in the database
        $this->assertDatabaseHas('sequences', [
            'type' => 'test',
            'current_number' => 1,
        ]);
    }

    /** @test */
    public function it_can_generate_sequential_numbers()
    {
        $number1 = $this->service->generateNumber('test');
        $number2 = $this->service->generateNumber('test');
        $number3 = $this->service->generateNumber('test');

        $this->assertEquals('1', $number1);
        $this->assertEquals('2', $number2);
        $this->assertEquals('3', $number3);

        // Check that the sequence was updated in the database
        $this->assertDatabaseHas('sequences', [
            'type' => 'test',
            'current_number' => 3,
        ]);
    }

    /** @test */
    public function it_can_create_a_sequence_with_custom_settings()
    {
        $this->service->createSequence('custom', 'PREFIX-', '-SUFFIX', 100, true);

        $number = $this->service->generateNumber('custom');

        // The number should include the prefix and suffix, and start from 100
        $this->assertStringStartsWith('PREFIX-100', $number);
        $this->assertStringEndsWith('-SUFFIX', $number);

        // Since random is true, there should be a random part
        $this->assertNotEquals('PREFIX-100-SUFFIX', $number);
    }

    /** @test */
    public function it_uses_start_number_for_first_generation()
    {
        // Create a sequence with a custom start number
        $this->service->createSequence('start_test', null, null, 500, false);

        $number = $this->service->generateNumber('start_test');

        $this->assertEquals('500', $number);

        // Check that the current number was updated
        $this->assertDatabaseHas('sequences', [
            'type' => 'start_test',
            'start_number' => 500,
            'current_number' => 500,
        ]);
    }

    /** @test */
    public function it_can_handle_multiple_sequence_types_independently()
    {
        $invoiceNumber = $this->service->generateNumber('invoice');
        $orderNumber = $this->service->generateNumber('order');

        $this->assertEquals('1', $invoiceNumber);
        $this->assertEquals('1', $orderNumber);

        // Generate another invoice number
        $invoiceNumber2 = $this->service->generateNumber('invoice');

        $this->assertEquals('2', $invoiceNumber2);

        // Order number should still be at 1
        $this->assertDatabaseHas('sequences', [
            'type' => 'order',
            'current_number' => 1,
        ]);
    }
}
