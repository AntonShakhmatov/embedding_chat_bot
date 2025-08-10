<?php declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use App\Model\Services\EmbeddingsService;
use App\Model\ACLForm;


final class HomepagePresenter extends Nette\Application\UI\Presenter
{
    /** @var EmbeddingsService @inject */
    public $embeddingsService;

    public function createComponentChatWindowModalForm()
    {
        $form = new ACLForm();
        $form->addRadioList(
            'predefined',
            '',
            [
                'produkty' => 'Hledám si info o produktech',
                'contacts' => 'Hledám kontakty',
                'works' => 'Zajímá mě práce',
            ]
        )->setHtmlAttribute('class', 'form-check-input mb-2')
            ->setOption('rendered', true);
    
        $form->addText('text', '')
            ->setHtmlAttribute('placeholder', 'Zadejte dotaz')
            ->setHtmlAttribute('class', 'form-control input-md messageInput');
    
        $form->addSubmit('send', '')
                ->setHtmlAttribute('class', 'sendButton ajax');
    
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'chatFormSucceeded'];
    
        return $form;
    }        

    /** @var Nette\Database\Context @inject */
        public $db;

    public function chatFormSucceeded(Form $form, \stdClass $values): void
    {
        $text = $values->text;

        // $result = $this->geminiService->getKeywords($prompt);

        // $this->sendJson(['text' => $result]);

        $user_embedding = $this->embeddingsService->get_embedding([$text])[0];

        $products = $this->db->query("SELECT * FROM embedding")->fetchAll();

        $articles = $this->db->query("SELECT * FROM embedding_article")->fetchAll();

        $contacts = $this->db->query("SELECT * FROM embedding_article")->fetchAll();

        $works = $this->db->query("SELECT * FROM embedding_article")->fetchAll();

        $scores = [];
        foreach ($products as $p) {
            $embedding = json_decode($p['union_date'], true);
            if (!$embedding || !is_array($embedding)) {
                continue;
            }
            $score = $this->embeddingsService->cosine_similarity($user_embedding, $embedding);
            // $score = $this->embeddingsService->findMostSimilarProducts($embedding);
            $scores[] = ['score' => $score, 'id' => $p['id'], 'union_date' => $p['union_date']];
        }

        foreach ($articles as $article) {
            $embedding = json_decode($article['union_date'], true);
            if (!$embedding || !is_array($embedding)) {
                continue;
            }
            $score = $this->embeddingsService->cosine_similarity($user_embedding, $embedding);
            // $score = $this->embeddingsService->findMostSimilarProducts($embedding);
            $scores[] = ['score' => $score, 'id' => $article['id'], 'union_date' => $article['union_date']];
        }

        foreach ($contacts as $c) {
            $embedding = json_decode($c['union_date'], true);
            if (!$embedding || !is_array($embedding)) {
                continue;
            }
            $score = $this->embeddingsService->cosine_similarity($user_embedding, $embedding);
            // $score = $this->embeddingsService->findMostSimilarProducts($embedding);
            $scores[] = ['score' => $score, 'id' => $c['id'], 'union_date' => $c['union_date']];
        }

        foreach ($works as $w) {
            $embedding = json_decode($w['union_date'], true);
            if (!$embedding || !is_array($embedding)) {
                continue;
            }
            $score = $this->embeddingsService->cosine_similarity($user_embedding, $embedding);
            // $score = $this->embeddingsService->findMostSimilarProducts($embedding);
            $scores[] = ['score' => $score, 'id' => $w['id'], 'union_date' => $w['union_date']];
        }

        usort($scores, fn($a, $b) => $b['score'] <=> $a['score']);

        $top5 = array_slice($scores, 0, 5);
    }
}
