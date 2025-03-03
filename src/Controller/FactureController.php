<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Commande;
use App\Repository\CommandeRepository;
use TCPDF;

final class FactureController extends AbstractController
{
    #[Route('/facture/{id}', name: 'telecharger_facture')]
    public function telechargerFacture(int $id, CommandeRepository $commandeRepository): Response
    {
        // Récupérer la commande depuis la base de données
        $commande = $commandeRepository->find($id);

        if (!$commande) {
            throw $this->createNotFoundException('Commande introuvable.');
        }

        // Initialisation de TCPDF
        $pdf = new TCPDF();
        $pdf->SetCreator('TuniSight');
        $pdf->SetAuthor($commande->getUser()->getNom());
        $pdf->SetTitle('Facture ' . $commande->getId());
        $pdf->SetHeaderData('', 0, 'Facture TuniSight', 'Commande n°' . $commande->getId());
        $pdf->setHeaderFont(['helvetica', '', 10]);
        $pdf->setFooterFont(['helvetica', '', 8]);
        $pdf->SetMargins(10, 30, 10); // Augmenter la marge supérieure pour le logo
        $pdf->SetAutoPageBreak(true, 20);
        $pdf->AddPage();

        // Ajouter le logo
       // Ajouter le logo


        // Contenu du PDF
        $html = '
        <style>
            h1 {
                text-align: center;
                color: #007bff; /* Bleu */
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }
            th, td {
                border: 1px solid #007bff; /* Bordure bleue */
                padding: 8px;
                text-align: left;
            }
            th {
                background-color: #007bff; /* Fond bleu pour les en-têtes */
                color: white; /* Texte blanc */
            }
            tr:nth-child(even) {
                background-color: #f2f2f2; /* Fond gris clair pour les lignes paires */
            }
            .total {
                text-align: right;
                margin-top: 20px;
                font-size: 1.2em;
                color: #007bff; /* Bleu */
            }
        </style>
        <h1>Facture</h1>
        <p><strong>Client:</strong> ' . $commande->getNom() . ' ' . $commande->getPrenom() . '</p>
        <p><strong>Email:</strong> ' . $commande->getEmail() . '</p>
        <p><strong>Date:</strong> ' . $commande->getDateCreation()->format('d/m/Y') . '</p>
        <p><strong>Adresse de livraison:</strong> ' . $commande->getAdresseliv() . '</p>
        <table>
            <tr>
                <th>Produit</th>
                <th>Quantité</th>
                <th>Prix Unitaire</th>
                <th>Total</th>
            </tr>';

        foreach ($commande->getLigneCommandes() as $ligneCommande) {
            $produit = $ligneCommande->getProduit();
            $quantite = $ligneCommande->getNbrProduits();
            $prixUnitaire = $produit->getPrix();
            $totalLigne = $prixUnitaire * $quantite;

            $html .= '
            <tr>
                <td>' . $produit->getLibelle() . '</td>
                <td>' . $quantite . '</td>
                <td>' . $prixUnitaire . '€</td>
                <td>' . $totalLigne . '€</td>
            </tr>';
        }

        $html .= '
        </table>
        <div class="total">
            <h2>Total: ' . $commande->getPrixtotale() . '€</h2>
        </div>';

        $pdf->writeHTML($html, true, false, true, false, '');

        // Générer le PDF et le retourner
        return new Response(
            $pdf->Output('facture_' . $id . '.pdf', 'D'),
            200,
            ['Content-Type' => 'application/pdf']
        );
    }
}