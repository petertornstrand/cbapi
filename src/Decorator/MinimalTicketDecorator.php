<?php

namespace App\Decorator;

use App\Attribute\Decorator;

/**
 * Class responsible for decorating ticket data.
 */
#[Decorator("ticket_min")]
class MinimalTicketDecorator extends DecoratorBase implements DecoratorInterface {

    /**
     * Decorates the ticket data with a link in HTML format.
     */
    #[\Override]
    public function decorate(array &$data): void {
        $data['htmlLink'] = $this->twig->render('ticket-link.html.twig', $data);
    }

}
