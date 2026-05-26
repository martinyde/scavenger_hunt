<?php

declare(strict_types=1);

namespace App\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Exposes the active race theme to Twig.
 *
 * Resolution order:
 *   1. `?theme=<id>` query parameter on the current request, when present.
 *   2. The RACE_THEME_DEFAULT env var (`app.race_theme_default` parameter).
 *
 * The chosen id is rendered as `<html data-theme="…">` in base.html.twig.
 * All race-display surfaces consume CSS variables defined under the matching
 * [data-theme="<id>"] selector — adding a new theme is purely a CSS swap.
 */
class RaceThemeExtension extends AbstractExtension
{
    private const QUERY_PARAM = 'theme';

    /**
     * Theme ids that may be selected via `?theme=…`. Keeping a small allow-list
     * prevents arbitrary user input from being reflected into the
     * `data-theme` attribute. Add new ids here when shipping a new theme.
     *
     * @var array<int, string>
     */
    private const ALLOWED_THEMES = ['default'];

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly string $defaultTheme,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('race_theme', $this->resolve(...)),
        ];
    }

    public function resolve(): string
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null !== $request) {
            $requested = (string) $request->query->get(self::QUERY_PARAM, '');
            if ('' !== $requested && in_array($requested, self::ALLOWED_THEMES, true)) {
                return $requested;
            }
        }

        $fallback = '' !== $this->defaultTheme ? $this->defaultTheme : 'default';

        return in_array($fallback, self::ALLOWED_THEMES, true) ? $fallback : 'default';
    }
}
