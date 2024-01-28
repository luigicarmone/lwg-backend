<?php

namespace App\Application\Controller;

use App\Core\Entity\Contact;
use App\Exceptions\CustomException;
use App\Model\Email\EmailBody;
use App\Model\Response200;
use App\Model\Response400;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ContactMe extends AbstractController
{
    #[Route('/api/v1/contact', name: 'contact', methods: ['PUT'])]
    public function sendEmail(EntityManagerInterface $entityManager, Request $request, SerializerInterface $serializer): JsonResponse
    {
        $entityManager->beginTransaction();

        try {
            $contact = new Contact();

            /** @var EmailBody $oEmailBody */
            $oEmailBody = $serializer->deserialize($request->getContent(), EmailBody::class, "json");

            $email = (new Email())
                ->from(new Address($oEmailBody->getEmail(), $oEmailBody->getName()))
                ->to('luigicarmone16@gmail.com')
                ->subject($oEmailBody->getCompany())
                ->text($oEmailBody->getNote());

            $contact->setName($oEmailBody->getName());
            $contact->setEmail($oEmailBody->getEmail());
            $contact->setCompany($oEmailBody->getCompany());
            $contact->setNote($oEmailBody->getNote());

            $entityManager->persist($contact);
            $entityManager->flush();

            $dsn = $_ENV['MAILER_DSN'];
            $transport = Transport::fromDsn($dsn);
            $mailer = new Mailer($transport);
            $mailer->send($email);

            $entityManager->commit();

            $oResponse = new Response200();
            $oResponse->setResponseCode("OK");
            $oResponse->setMessage("[LWG/EMAIL] Operazione avvenuta con successo");
            $oResponse->setData($mailer);
            return $this->json($contact);

        } catch (CustomException $e) {
            $entityManager->rollback();
            $oResponse400 = new Response400();
            $oResponse400->setError($e->getErrorMessage());
            $oResponse400->setMessage($e->getMessage());
            return $this->json($oResponse400, 400);

        } catch (\Exception $e) {
            $entityManager->rollback();
            $oResponse400 = new Response400();
            $oResponse400->setError(Response400::$DEFAULT_GENERIC_ERROR);
            $oResponse400->setMessage("[LWG/EMAIL] Errore durante l'invio dell'email, riprovare più tardi: " . $e->getMessage());
            return $this->json($oResponse400, 400);
        }
    }
}
