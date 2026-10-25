<?php
declare(strict_types=1);

namespace Konf\Controller;

use Konf\Http;
use Konf\Repo\Dict;

final class PageController
{
    public function home(): never
    {
        Http::render('home', ['title' => null, 'rooms' => Dict::rooms()]);
    }
}
