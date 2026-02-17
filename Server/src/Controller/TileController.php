<?php
/**
 * @license AVT
 */

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Filesystem\Filesystem;

class TileController extends AbstractController
{
    /**
     * @param HttpClientInterface $client
     * @param int $z
     * @param int $x
     * @param int $y
     *
     * @Route("/tiles/{z}/{x}/{y}.png", name="app_tile")
     *
     * @return Response
     */
    public function index(HttpClientInterface $client, LoggerInterface $log, $z, $x, $y): Response
    {

        $tilesPath = 'Tiles/';
        $tileDirs = $z . '/' . $x;
        $tileFilePath = $tileDirs . '/' . $y . '.png';
        $osmURL = 'https://tile.openstreetmap.org/';
        $fileSystem = new Filesystem();
        if (!$fileSystem->exists($tilesPath . $tileFilePath)) {
            try {
                $response = $client->request(
                    'GET',
                    $osmURL . $tileFilePath
                );
                $content = $response->getContent();
                if (!$fileSystem->exists($tilesPath . $tileDirs)) {
                    $fileSystem->mkdir($tilesPath . $tileDirs);
                }
                $fileSystem->dumpFile($tilesPath . $tileFilePath, $content);
            } catch (\Throwable $exception) {

                $log->error("EXCEPTION! MESSAGE: " . $exception->getMessage() . " FILE: " . $exception->getFile(). " LINE: ".$exception->getLine());
            }
        }

        return new BinaryFileResponse($tilesPath . $tileFilePath);
    }
}
