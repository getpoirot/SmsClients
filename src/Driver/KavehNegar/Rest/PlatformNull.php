<?php
namespace Poirot\Sms\Driver\KavehNegar\Rest;

use Poirot\ApiClient\Interfaces\iPlatform;
use Poirot\ApiClient\Interfaces\Request\iApiCommand;
use Poirot\ApiClient\Interfaces\Response\iResponse;
use Poirot\ApiClient\Request\Command;

use Poirot\Std\ConfigurableSetter;

// TODO mock platform commands
class PlatformNull
    extends ConfigurableSetter
    implements iPlatform
{
    /** @var Command */
    protected $Command;

    // Options:
    protected $usingSsl  = false;
    protected $serverUrl = null;


    /**
     * Build Platform Specific Expression To Send Trough Transporter
     *
     * @param iApiCommand $command Method Interface
     *
     * @return iPlatform Self or Copy/Clone
     */
    function withCommand(iApiCommand $command)
    {
        $self = clone $this;
        $self->Command = $command;
        return $self;
    }

    /**
     * Build Response with send Expression over Transporter
     *
     * - Result must be compatible with platform
     * - Throw exceptions if response has error
     *
     * @throws \Exception Command Not Set
     * @return iResponse
     */
    function send()
    {
        if (!$command = $this->Command)
            throw new \Exception('No Command Is Specified.');


        # Get Connect To Server and Send Data

        $cmArguments = $command->getArguments();

        $data = [];
        foreach ($cmArguments as $key => $val) {
            // Filter null values ...
            if ($val === null) continue;

            $data[$key] = $val;
        }

        $response = new Response(
            '{
              "entries": [
                 {
                   "messageid": 1,
                   "message": "message",
                   "sender": "xxx",
                   "receptor": "xxx",
                   "status": "stat.sent",
                   "date": "1547297176"
                 }
              ]
            }'
            , 200
            , ['content_type' => 'application/json']
            , null
        );

        return $response;
    }
}
