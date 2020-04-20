<?php


namespace App\MessageHandler;


use App\Message\CommentMessage;
use App\Repository\CommentRepository;
use App\SpamChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class CommentMessageHandler implements MessageHandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var SpamChecker
     */
    private $spamChecker;
    /**
     * @var CommentRepository
     */
    private $commentRepository;

    public function __construct(
        EntityManagerInterface $em,
        SpamChecker $spamChecker,
        CommentRepository $commentRepository
    ) {
        $this->em = $em;
        $this->spamChecker = $spamChecker;
        $this->commentRepository = $commentRepository;
    }

    public function __invoke(CommentMessage $message)
    {
        $comment = $this->commentRepository->find($message->getId());

        if (!$comment) {
            return;
        }

        if (2 === $this->spamChecker->getSpamScore($comment, $message->getContext())) {
            $comment->setState('spam');
        } else {
            $comment->setState('published');
        }

        $this->em->flush();
    }
}