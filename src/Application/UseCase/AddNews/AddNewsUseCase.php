<?php

namespace App\Application\UseCase\AddNews;

use App\Application\Command\CreateNewsCommand;
use App\Application\Command\CreateNewsHandler;
use App\Application\Gateway\UrlGatewayInterface;
use App\Application\Gateway\UrlGatewayRequest;

class AddNewsUseCase
{
    public function __construct(
        private readonly CreateNewsHandler $createNewsHandler,
        private readonly UrlGatewayInterface $urlGateway,
    ) {
    }
    public function __invoke(AddNewsRequest $request): AddNewsResponse
    {
        $urlGatewayRequest = new UrlGatewayRequest($request->url);
        $urlGatewayResponse = $this->urlGateway->visitLink($urlGatewayRequest);
        //ASK validation
        $command = new CreateNewsCommand($request->url, $urlGatewayResponse->title);
        $news = $this->createNewsHandler->handle($command);
        return new AddNewsResponse($news->getId());
    }
}
