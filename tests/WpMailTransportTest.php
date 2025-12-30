<?php

declare(strict_types=1);

/*
 * This file is part of the WP Mail Transport package.
 *
 * (ɔ) Frugan <dev@frugan.it>
 *
 * This source file is subject to the GNU GPLv3 license that is bundled
 * with this source code in the file COPYING.
 */

namespace WpSpaghetti\WpMailTransport\Tests;

use PHPUnit\Framework\TestCase;
use WpSpaghetti\WpMailTransport\Transport\WpMailTransport;

class WpMailTransportTest extends TestCase
{
    public function test_transport_string_representation(): void
    {
        $transport = new WpMailTransport;
        $this->assertEquals('wp-mail', (string) $transport);
    }

    public function test_transport_can_be_instantiated(): void
    {
        $transport = new WpMailTransport;
        $this->assertInstanceOf(WpMailTransport::class, $transport);
    }

    public function test_transport_can_be_instantiated_with_debug(): void
    {
        $transport = new WpMailTransport(true);
        $this->assertInstanceOf(WpMailTransport::class, $transport);
    }

    public function test_transport_can_be_instantiated_without_debug(): void
    {
        $transport = new WpMailTransport(false);
        $this->assertInstanceOf(WpMailTransport::class, $transport);
    }
}
