<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class ApiController extends AbstractController
{
    public function __construct(
        protected HttpClientInterface $client,
    ) {
    }

    #[Route('/{project}/tickets')]
    public function tickets(string $project): JsonResponse
    {
        $response = $this->doApiCall("/{$project}/tickets");
        $xml = new \SimpleXMLElement($response);
        $arr = [];
        foreach ($xml->ticket as $ticket) {
            $arr[] = (object) [
                'id' => $ticket->{'ticket-id'},
                'summary' => $ticket->summary,
            ];
        }
        return new JsonResponse($xml);
    }

    #[Route('/{project}/assignments')]
    public function assignments(string $project): JsonResponse
    {
        $response = $this->doApiCall("/{$project}/assignments");

        return new JsonResponse(['project' => $project]);
    }

    protected function doApiCall(string $path) : string {
        $auth = base64_encode($this->getParameter('cb.username') . ':' . $this->getParameter('cb.api_key'));
        $client = $this->client->withOptions([
            'base_uri' => $this->getParameter('cb.base_url'),
            'headers' => [
                'Accept' => 'application/xml',
                'Content-type' => 'application/xml',
                'Authorization' => 'Basic ' . $auth,
            ],
        ]);
        $response = $client->request('GET', $path);
        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Response code ' . $response->getStatusCode());
        }
        return $response->getContent();
    }
}
