<?php

namespace App\Service;



use App\Strategy\AiServicesStrategy;

class AiService extends BaseService
{


    protected AiServicesStrategy $aiService;

    public function __construct(AiServicesStrategy $aiService)
    {
        $this->aiService = $aiService;
    }


    public function forward($params)
    {
        $this->aiService->forward($params);
    }

}
