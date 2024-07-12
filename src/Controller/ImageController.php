<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class ImageController extends AbstractController
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->render('index.html.twig');
    }

    #[Route('/fetch', name: 'fetch', methods: ['POST'])]
    public function fetchImages(Request $request): Response
    {
        $content = $request->getContent();
        $data = json_decode($content, true);

        if (!$data || !isset($data['url'])) {
            return new Response('URL parameter is missing or invalid.', Response::HTTP_BAD_REQUEST);
        }

        if (strpos($data['url'], 'https://') !== 0 || strpos($data['url'], 'http://') !== 0) {
            $url =  'https://' .$data['url'] . '/';
        } else {
            $url = $data['url'] . '/';
        }

        $response = $this->httpClient->request('GET', $url);

        $content = $response->getContent();

        $images = $this->extractImages($content, $url);

        return new JsonResponse($images);
    }

    private function extractImages(string $content, $url): array
    {
        preg_match_all('/<img[^>]+src="([^">]+)"/i', $content, $matches);

        $validExtensions = ['jpeg', 'jpg', 'webp', 'bmp', 'svg'];
        $filteredImages = array_filter($matches[1], function($image) use ($validExtensions) {
            $extension = strtolower(pathinfo($image, PATHINFO_EXTENSION));
            return in_array($extension, $validExtensions);
        });

        $prefix = $url;
        $filteredImages = array_map(function($image) use ($prefix) {
            return $prefix . ltrim($image, '/');
        }, $filteredImages);

        $filteredImages = $this->getImageData($filteredImages);

        return $filteredImages;

    }

    private function getImageData(array $images): array
    {
        $imageData = [
            'images' => [],
            'totalSize' => 0
        ];

        foreach ($images as $image) {
            try {
                $response = $this->httpClient->request('HEAD', $image);
                $size = $response->getHeaders()['content-length'][0] ?? 0;
                $imageData['totalSize'] += (int)$size;
                $imageData['images'][] = $image;
            } catch (\Exception $e) {

            }
        }

        $imageData['count'] = count($imageData['images']);

       return $imageData;
    }
}
