<?php

namespace App\Application\Controller;

use OpenApi\Attributes\Put;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ContactMe extends Controller
{
    #[Route('/api/v1/contact',
        name: 'contact',
        methods: ['POST', 'PUT'])
    ]

    public function sendEmail(Request $request)
    {

        $consumes = ['application/json'];
        $inputFormat = $request->headers->has('Content-Type') ? $request->headers->get('Content-Type') : $consumes[0];
        if (!in_array($inputFormat, $consumes)) {
            return new Response('', 415);
        }

        $produces = ['application/json'];

        $clientAccepts = $request->headers->has('Accept') ? $request->headers->get('Accept') : '*/*';
        $responseFormat = $this->getOutputFormat($clientAccepts, $produces);
        if ($responseFormat === null) {
            return new Response('', 406);
        }

        $contactData = json_decode($request->getContent(), true);

        $email = (new Email())
            ->from(new Address($contactData['email'], $contactData['name']))
            ->to('luigicarmone16@gmail.com')
            ->subject($contactData['company'])
            ->text($contactData['note']);

        $dsn = 'smtp://lwg-mailhog:1025';
        $transport = Transport::fromDsn($dsn);
        $mailer = new Mailer($transport);

        try {
            $mailer->send($email);

            return new Response('OK', 200);

        } catch (TransportExceptionInterface $e) {
            return new Response('KO' . $e->getMessage(), 500);
        }
    }
}
