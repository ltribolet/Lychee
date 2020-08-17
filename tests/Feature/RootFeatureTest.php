<?php

namespace Tests\Feature;

class RootFeatureTest extends FeatureTestCase
{
    /**
     * Test album functions.
     *
     * @return void
     */
    public function testRoot(): void
    {
        exec('php index.php 2>&1', $return);
        $return = implode('', $return);
        $this->assertStringContainsString(
            'This is the root directory and it MUST NOT BE PUBLICALLY ACCESSIBLE',
            $return
        );
    }
}
