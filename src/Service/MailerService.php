<?php
namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class MailerService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendVoucherEmail(string $recipient, string $codeVoucher, int $valeurReduction, \DateTimeInterface $dateExpiration): void
    {
        $email = (new Email())
            ->from(new Address('mailtrap@example.com','MailTrap'))
            ->to($recipient)
            ->subject('Votre Voucher de Réduction')
            ->html("
                <h1>Félicitations ! 🎉</h1>
                <p>Vous avez reçu un voucher de réduction de <strong>{$valeurReduction}%</strong> !</p>
                <p><strong>Code :</strong> {$codeVoucher}</p>
                <p><strong>Date d'expiration :</strong> {$dateExpiration->format('d/m/Y')}</p>
                <p>Utilisez-le avant son expiration !</p>
            ");

        $this->mailer->send($email);
    }
}
