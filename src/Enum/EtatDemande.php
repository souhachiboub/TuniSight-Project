<?php
namespace App\Enum;

enum EtatDemande: string
{
    case ACCEPTE='accepte';
    case REFUSE='refuse';
    case ATTENTE='attente';
    
    
   
}