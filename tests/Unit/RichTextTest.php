<?php

namespace Tests\Unit;

use App\Site\RichText;
use PHPUnit\Framework\TestCase;

class RichTextTest extends TestCase
{
    public function test_heading_ids_are_unique_and_table_headers_are_semantic(): void
    {
        $html = RichText::render('<h2>Week 1: core features</h2><h3>Week 1: core features</h3><p>André’s work</p><table><tbody><tr><th>Day</th></tr><tr><td>2</td></tr></tbody></table>');
        $this->assertStringContainsString('id="week-1-core-features"', $html);
        $this->assertStringContainsString('id="week-1-core-features-1"', $html);
        $this->assertStringContainsString('André’s work', $html);
        $this->assertStringContainsString('<thead><tr><th>Day</th></tr></thead>', $html);
        $this->assertStringContainsString('<tbody><tr><td>2</td></tr></tbody>', $html);
    }
}
