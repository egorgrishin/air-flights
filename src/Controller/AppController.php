<?php
declare(strict_types=1);

namespace App\Controller;

use App\PobedaSearch;
use App\SmartaviaSearch;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
//use Symfony\Component\HttpFoundation\Request;

class AppController extends AbstractController
{
    #[Route('/main', name: 'main')]
    public function main()
    {
        $request = Request::createFromGlobals();
        $date = $request->query->get('date');
        $dateTime = Datetime::createFromFormat('Y-m-d', $date);
        (new PobedaSearch())->run($dateTime);
        (new SmartaviaSearch())->run($dateTime);
    }
}