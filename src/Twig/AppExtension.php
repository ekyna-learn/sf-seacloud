<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\Server;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

use function sprintf;

/**
 * Class AppExtension
 * @package App\Twig
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('server_state_badge', [$this, 'renderStateBadge'], ['is_safe' => ['html']]),
        ];
    }

    public function renderStateBadge(Server $server): string
    {
        $label = 'Pending';
        $theme = 'warning';

        if ($server->getState() === Server::STATE_STOPPED) {
            $label = 'Stopped';
            $theme = 'danger';
        } elseif ($server->getState() === Server::STATE_READY) {
            $label = 'Ready';
            $theme = 'success';
        }

        return sprintf('<span class="badge bg-%s">%s</span>', $theme, $label);
    }
}
