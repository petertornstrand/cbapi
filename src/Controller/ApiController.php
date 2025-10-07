<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Transformer\TicketTransformer;

class ApiController extends AbstractController
{
    public function __construct(
        protected HttpClientInterface $client,
        protected TicketTransformer $transformer
    ) {
    }

    #[Route('/{project}/ticket/{ticketId}')]
    public function ticket(string $project, string $ticketId): JsonResponse
    {
        $response = $this->doApiCall("/{$project}/tickets?query=id:{$ticketId}");
        $xml = new \SimpleXMLElement($response);
        $ticket = $this->transformer->transform((array)$xml);
        return new JsonResponse($ticket);
    }

    #[Route('/{project}/tickets')]
    public function tickets(string $project): JsonResponse
    {
        $response = $this->doApiCall("/{$project}/tickets");
        $xml = new \SimpleXMLElement($response);
        $results = [];
        foreach ($xml->ticket as $i => $ticket) {
            $results[] = $this->transformer->transform((array)$xml->ticket[$i]);

        }
        return new JsonResponse($results);
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
