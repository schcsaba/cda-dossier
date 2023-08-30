<?php

namespace Tests\Unit\UploadSteps;

use App\Events\StepErrorEvent;
use App\UploadSteps\CheckEmail;
use App\UploadSteps\Utils\ShortErrorMessage;
use Exception;
use Illuminate\Support\Facades\Event;

class CheckEmailTest extends StepTest
{

    public function setUp(): void
    {
        parent::setUp();

        $dummyDataWithWrongEmail = [
            "Civilit\u00e9*,Nom*,Pr\u00e9nom*,Pr\u00e9nom Nom,e-mail*,Adresse postale,Code Postal,Ville,N\u00b0 Permis,Autoriser l'application SuiviConducteur,Consulter les POI,G\u00e9rer les POI,Consulter les stations services,Consulter le rapport journalier,Consulter les statistiques OptiDriving,Utilisation du mode Quick Privacy,Identification SuiviConducteur",
            "M,HUGO,Victor,Victor HUGO,victor.hugo@gmail.com,178 Avenue Maginot,37100,TOURS,21AE99866,TRUE,TRUE,FALSE,TRUE,TRUE,TRUE,FALSE,TRUE",
            "M,CAMUS,Albert,Albert CAMUS,albert.camus@gmail,178 Avenue Maginot,37100,TOURS,,TRUE,TRUE,FALSE,TRUE,TRUE,TRUE,FALSE,TRUE",
            "M,VERNE,Jules,Jules VERNE,jules.verne@gmail.com,178 Avenue Maginot,37100,TOURS,15AC95054,TRUE,TRUE,FALSE,TRUE,TRUE,TRUE,FALSE,TRUE"
        ];
        $this->createInputFile(json_encode($dummyDataWithWrongEmail));
    }

    public function testCheckEmailWithWrongEmail()
    {
        $params = [
            'separator' => ',',
            'email_field' => 'e-mail*'
        ];
        $step = new CheckEmail($this->getUpload()->id, 'onboarding.drivers', $params, [], $this->getInputFileName());

        $this->assertThrows(function () use ($step) {
            $step->process();
        }, Exception::class, ShortErrorMessage::FILE_CONTENT_ERROR . ShortErrorMessage::GLUE . 'Invalid email: albert.camus@gmail');
    }
}