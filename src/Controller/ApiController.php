<?php

namespace App\Controller;

use App\Decorator\DecoratorInterface;
use App\DecoratorFactory;
use App\Exception\MissingCredentialsException;
use App\TransformerFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controller for handling API requests.
 */
class ApiController extends AbstractController
{
    /**
     * Constructor.
     */
    public function __construct(
        protected HttpClientInterface $client,
        protected TransformerFactory $transformerFactory,
        protected DecoratorFactory $decoratorFactory,
    ) {
    }

    /**
     * Handles the retrieval of context data for a specific project and
     * ticket ID.
     *
     * @param Request $request The HTTP request object.
     * @param string $project The project identifier.
     * @param int $ticketId The ticket ID.
     *
     * @return JsonResponse The JSON response containing the ticket context data.
     */
    #[Route('/{project}/ticket/{ticketId}/context', methods: ['GET'], condition: "params['ticketId'] > 0")]
    public function context(Request $request, string $project, int $ticketId): JsonResponse {
        try {
            $this->authorize($request);
            $context = [
                'ticket' => json_decode($this->ticket($request, $project, $ticketId)->getContent()),
                'assignments' => json_decode($this->assignments($request, $project)->getContent()),
                'statuses' => json_decode($this->statuses($request, $project)->getContent()),
                'priorities' => json_decode($this->priorities($request, $project)->getContent()),
                'categories' => json_decode($this->categories($request, $project)->getContent()),
                'types' => json_decode($this->types($request, $project)->getContent()),
                'comments' => json_decode($this->comments($request, $project, $ticketId)->getContent()),
                'project' => json_decode($this->project($request, $project)->getContent()),
                'links' => null,
                'participants' => null,
            ];
            $context['links'] = $this->extractTicketLinks($context['comments']);
            $context['participants'] = $this->extractParticipants($context['comments'], $context['assignments']);
            $decorator = $this->decoratorFactory->create('context', false);
            if ($decorator instanceof DecoratorInterface) {
                $decorator->decorate($context);
            }
        }
        catch (\Throwable $e) {
            return new JsonResponse((array)$e);
        }

        return new JsonResponse($context);
    }

    /**
     * Handles the ticket retrieval for a specific project and ticket ID.
     *
     * @param Request $request The HTTP request object.
     * @param string $project The project identifier.
     * @param int $ticketId The ticket identifier.
     *
     * @return JsonResponse The transformed ticket data in JSON format.
     */
    #[Route('/{project}/ticket/{ticketId}', methods: ['GET'], condition: "params['ticketId'] > 0")]
    public function ticket(Request $request, string $project, int $ticketId): JsonResponse
    {
        try {
            $this->authorize($request);
            $response = $this->doApiCall("/{$project}/tickets?query=id:{$ticketId}");
            $xml = new \SimpleXMLElement($response);
            $transformer = $this->transformerFactory->create('ticket');
        }
        catch (\Throwable $e) {
            return $this->errorResponse($e);
        }

        $ticket = $transformer->transform((array)$xml->ticket[0]);
        return new JsonResponse($ticket);
    }

    /**
     * Handles the ticket retrieval for a specified project.
     *
     * @param Request $request The HTTP request object.
     * @param string $project The project identifier.
     * @param ?int $page The page number for pagination.
     * @param string|null $query The search query.
     *
     * @return JsonResponse A JSON response containing the transformed ticket data.
     */
    #[Route('/{project}/tickets', methods: ['GET', 'OPTIONS'])]
    public function tickets(Request $request, string $project, #[MapQueryParameter(name: "page")] ?int $page = null, #[MapQueryParameter(name: "query")] ?string $query = null): JsonResponse
    {
        try {
            $this->authorize($request);
            $params = $page ? ['page' => $page] : [];
            $params += $query ? ['query' => $query] : null;
            $response = $this->doApiCall("/{$project}/tickets", $params);
            $xml = new \SimpleXMLElement($response);
            $plugin = 'ticket';
            if ($prefer = $request->headers->get('Prefer', null)) {
                // TODO: Parse the header and get the transformer/decorator ID.
                //  Can we do additional things with this header? Should you
                //  be able to specify multiple plugins? Fields to include?
                $plugin = 'ticket_min';
            }
            $transformer = $this->transformerFactory->create($plugin);
            $decorator = $this->decoratorFactory->create($plugin, false);
        }
        catch (\Throwable $e) {
            return $this->errorResponse($e);
        }

        $results = [];
        foreach ($xml->ticket as $ticket) {
            $data = $transformer->transform((array)$ticket);
            if ($decorator instanceof DecoratorInterface) {
                $decorator->decorate($data);
            }
            $results[] = $data;
        }
        return new JsonResponse($results);
    }

    /**
     * Handles the assignments' route for a specified project.
     *
     * @param Request $request The HTTP request object.
     * @param string $project The project identifier.
     *
     * @return JsonResponse The response containing the project assignments data.
     */
    #[Route('/{project}/assignments', methods: ['GET'])]
    public function assignments(Request $request, string $project): JsonResponse
    {
        try {
            $this->authorize($request);
            $response = $this->doApiCall("/{$project}/assignments");
            $xml = new \SimpleXMLElement($response);
            $transformer = $this->transformerFactory->create('assignment');
            $decorator = $this->decoratorFactory->create('assignment');
        }
        catch (\Throwable $e) {
            return $this->errorResponse($e);
        }

        $results = [];
        foreach ($xml->user as $obj) {
            $data = $transformer->transform((array)$obj);
            $decorator->decorate($data);
            $results[] = $data;
        }
        return new JsonResponse($results);
    }

    /**
     * Handles the categories' route for a specified project.
     *
     * @param Request $request The HTTP request object.
     * @param string $project The project identifier.
     *
     * @return JsonResponse The response containing the project categories data.
     */
    #[Route('/{project}/categories', methods: ['GET'])]
    public function categories(Request $request, string $project): JsonResponse
    {
        try {
            $this->authorize($request);
            $response = $this->doApiCall("/{$project}/tickets/categories");
            $xml = new \SimpleXMLElement($response);
            $transformer = $this->transformerFactory->create('category');
        }
        catch (\Throwable $e) {
            return $this->errorResponse($e);
        }

        $results = [];
        foreach ($xml->{'ticketing-category'} as $obj) {
            $results[] = $transformer->transform((array)$obj);
        }
        return new JsonResponse($results);
    }

    /**
     * Handles the priorities' route for a specified project.
     *
     * @param Request $request The HTTP request object.
     * @param string $project The project identifier.
     *
     * @return JsonResponse The response containing the project priorities data.
     */
    #[Route('/{project}/priorities', methods: ['GET'])]
    public function priorities(Request $request, string $project): JsonResponse
    {
        try {
            $this->authorize($request);
            $response = $this->doApiCall("/{$project}/tickets/priorities");
            $xml = new \SimpleXMLElement($response);
            $transformer = $this->transformerFactory->create('priority');
        }
        catch (\Throwable $e) {
            return $this->errorResponse($e);
        }

        $results = [];
        foreach ($xml->{'ticketing-priority'} as $obj) {
            $results[] = $transformer->transform((array)$obj);
        }
        return new JsonResponse($results);
    }

    /**
     * Handles the statuses' route for a specified project.
     *
     * @param Request $request The HTTP request object.
     * @param string $project The project identifier.
     *
     * @return JsonResponse The response containing the project statuses data.
     */
    #[Route('/{project}/statuses', methods: ['GET'])]
    public function statuses(Request $request, string $project): JsonResponse
    {
        try {
            $this->authorize($request);
            $response = $this->doApiCall("/{$project}/tickets/statuses");
            $xml = new \SimpleXMLElement($response);
            $transformer = $this->transformerFactory->create('status');
        }
        catch (\Throwable $e) {
            return $this->errorResponse($e);
        }

        $results = [];
        foreach ($xml->{'ticketing-status'} as $obj) {
            $results[] = $transformer->transform((array)$obj);
        }
        return new JsonResponse($results);
    }

    /**
     * Handles the types' route for a specified project.
     *
     * @param Request $request The HTTP request object.
     * @param string $project The project identifier.
     *
     * @return JsonResponse The response containing the project types data.
     */
    #[Route('/{project}/types', methods: ['GET'])]
    public function types(Request $request, string $project): JsonResponse
    {
        try {
            $this->authorize($request);
            $response = $this->doApiCall("/{$project}/tickets/types");
            $xml = new \SimpleXMLElement($response);
            $transformer = $this->transformerFactory->create('type');
        }
        catch (\Throwable $e) {
            return $this->errorResponse($e);
        }

        $results = [];
        foreach ($xml->{'ticketing-type'} as $obj) {
            $results[] = $transformer->transform((array)$obj);
        }
        return new JsonResponse($results);
    }

    /**
     * Handles the comments' route for a specified project.
     *
     * @param Request $request The HTTP request object.
     * @param string $project The project identifier.
     *
     * @return JsonResponse The response containing the ticket comments data.
     */
    #[Route('/{project}/ticket/{ticketId}/comments', methods: ['GET'], condition: "params['ticketId'] > 0")]
    public function comments(Request $request, string $project, int $ticketId): JsonResponse
    {
        try {
            $this->authorize($request);
            $response = $this->doApiCall("/{$project}/tickets/{$ticketId}/notes");
            $xml = new \SimpleXMLElement($response);
            $transformer = $this->transformerFactory->create('note');
        }
        catch (\Throwable $e) {
            return $this->errorResponse($e);
        }

        $results = [];
        foreach ($xml->{'ticket-note'} as $obj) {
            $results[] = $transformer->transform((array)$obj);
        }
        return new JsonResponse($results);
    }

    /**
     * Handles the project route for a specified project.
     *
     * @param Request $request The HTTP request object.
     * @param string $project The project identifier.
     *
     * @return JsonResponse The response containing the project data.
     */
    #[Route('/{project}', methods: ['GET'], condition: "params['project'] != 'groups'")]
    public function project(Request $request, string $project): JsonResponse
    {
        try {
            $this->authorize($request);
            $response = $this->doApiCall("/{$project}");
            $xml = new \SimpleXMLElement($response);
            $transformer = $this->transformerFactory->create('project');
            $decorator = $this->decoratorFactory->create('project');
        }
        catch (\Throwable $e) {
            return $this->errorResponse($e);
        }

        $project = $transformer->transform((array)$xml->project[0]);
        $decorator->decorate($project);
        return new JsonResponse($project);
    }

    /**
     * Handles the projects' route.
     *
     * @param Request $request The HTTP request object.
     *
     * @return JsonResponse The response containing the projects' data.
     */
    #[Route('/projects', methods: ['GET'])]
    public function projects(Request $request): JsonResponse
    {
        try {
            $this->authorize($request);
            $response = $this->doApiCall("/projects");
            $xml = new \SimpleXMLElement($response);
            $transformer = $this->transformerFactory->create('project');
            $decorator = $this->decoratorFactory->create('project');
        }
        catch (\Throwable $e) {
            return $this->errorResponse($e);
        }

        $results = [];
        foreach ($xml->{'project'} as $obj) {
            $data = $transformer->transform((array)$obj);
            $decorator->decorate($data);
            $results[] = $data;
        }

        return new JsonResponse($results);
    }

    /**
     * Handles the group route.
     *
     * @param Request $request The HTTP request object.
     *
     * @return JsonResponse The response containing the group data.
     */
    #[Route('/groups', methods: ['GET'])]
    public function groups(Request $request): JsonResponse
    {
        try {
            $this->authorize($request);
            $response = $this->doApiCall("/project_groups");
            $xml = new \SimpleXMLElement($response);
            $transformer = $this->transformerFactory->create('group');
        }
        catch (\Throwable $e) {
            return $this->errorResponse($e);
        }

        $results = [];
        foreach ($xml->{'project-group'} as $obj) {
            $results[] = $transformer->transform((array)$obj);
        }
        return new JsonResponse($results);
    }

    /**
     * Extracts ticket links from the provided comments.
     *
     * @param array $comments
     *
     * @return array
     */
    protected function extractTicketLinks(array $comments): array {
        $tickets = [];
        foreach ($comments as $comment) {
            $matches = [];
            preg_match_all('/\s+#(\d+)/', $comment->content, $matches);
            if (!empty($matches[1])) {
                foreach ($matches[1] as $match) {
                    $tickets[] = $match;
                }
            }
        }
        return $tickets;
    }

    /**
     * Extracts participants from the provided comments.
     *
     * @param array $comments
     * @param array $assignments
     *
     * @return array
     */
    protected function extractParticipants(array $comments, array $assignments): array {
        $ids = [];
        array_walk($comments, function ($comment) use (&$ids) {
            if (!array_key_exists($comment->userId, $ids)) {
                $ids[$comment->userId] = $comment->userId;
            }
        });

        $participants = array_filter($assignments, function ($assignment) use ($ids) {
            return array_key_exists($assignment->id, $ids);
        });

        return array_values($participants);
    }

    /**
     * Executes an API call to the specified path using the configured HTTP client.
     *
     * @param string $path The API endpoint path to call.
     * @param array $params The parameters to include in the API call.
     *
     * @return string The content of the response from the API.
     *
     * @throws \Throwable
     */
    protected function doApiCall(string $path, array $params = []) : string {
        try {
            // Get Authorization header.
            $auth = $this->getAuthHeader();

            // Create the client.
            $client = $this->client->withOptions([
                'base_uri' => $this->getParameter('cb.base_url'),
                'headers' => [
                    'Accept' => 'application/xml',
                    'Content-type' => 'application/xml',
                    'Authorization' => $auth,
                ],
            ]);

            // Build the query string and append it to the path.
            $query = http_build_query($params);
            $path .= $query ? '?' . $query : '';

            // Make the request.
            $response = $client->request('GET', $path);
            if ($response->getStatusCode() == 404) {
                throw new NotFoundHttpException('Not Found: ' . $path);
            }
            return $response->getContent();
        }
        catch (\Throwable $e) {
            throw $e;
        }
    }

    /**
     * Get base64 encoded credentials
     *
     * @return string
     *
     * @throws MissingCredentialsException
     */
    protected function getAuthHeader(): string {
        $username = $this->getParameter('cb.username') ?? false;
        $api_key = $this->getParameter('cb.api_key') ?? false;

        // Make sure we got the credentials we need.
        if (!$username || !$api_key) {
            throw new MissingCredentialsException;
        }

        return 'Basic ' . base64_encode("{$username}:{$api_key}");
    }

    /**
     * Authorizes the API call by examining the request Authorization header.
     *
     * @param Request $request The HTTP request object.
     * @return void
     *
     * @throws MissingCredentialsException
     * @throws UnauthorizedHttpException
     */
    protected function authorize(Request $request) : void {
        $actual = $request->headers->get('X-API-Key');
        $expected = $this->getParameter('cbapi_key') ?? false;

        if (!$expected) {
            throw new MissingCredentialsException;
        }

        if ($actual !== $expected) {
            //throw new UnauthorizedHttpException('X-API-Key realm="cbapi"', 'Invalid API key.');
        }
    }

    /**
     * Construct a JSON response with an error message and code.
     *
     * @param \Throwable $exception
     *
     * @return JsonResponse
     */
    protected function errorResponse(\Throwable $exception) : JsonResponse {
        $data = (object) [
            'error' => $exception->getMessage(),
        ];
        $statusCode = 500;
        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
        }
        return new JsonResponse($data, $statusCode);
    }
}
