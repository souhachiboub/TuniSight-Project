<?php
namespace App\Enum;

enum TypeNotif: string
{
    case RECOMMONDATION= 'recommondation';
    case RECLAMATION='reclamation';
    case RESERVATION='reservation';
    case MESSAGE='message';
    case ACCEPTATION='acceptation';
    
   
}