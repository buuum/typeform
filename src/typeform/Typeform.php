<?php

namespace Buuum;

use Curl\Curl;

class Typeform
{

    const MAX_LIMIT_RANGE = 1000;
    /**
     * API KEY
     *
     * @var string
     */
    protected $api_key;

    /**
     * API URI
     *
     * @var string
     */
    protected $api_uri = 'https://api.typeform.com/v1/form/';

    /**
     * form id
     *
     * @var int
     */
    protected $typeform_id;

    /**
     * Current page
     *
     * @var int
     */
    protected $page = 0;

    /**
     * Result limit
     *
     * @var int
     */
    protected $limit = 50;

    /**
     * Start date
     *
     * @var
     */
    protected $since = null;

    /**
     * End date
     *
     * @var
     */
    protected $until = null;

    /**
     * Whether or not to get completed results
     *
     * @var bool
     */
    protected $completed = true;

    /**
     * Questions
     *
     * @var []
     */
    protected $questions = [];

    /**
     * Responses
     *
     * @var []
     */
    protected $responses = [];

    /**
     * Statistics
     *
     * @var []
     */
    protected $stats = [];


    /**
     * @var Curl
     */
    protected $curl;

    /**
     * Typeform constructor.
     * @param $api_key
     * @param $url_typeform
     */
    public function __construct($api_key, $url_typeform = false)
    {
        $this->api_key = $api_key;
        if ($url_typeform) {
            $this->typeform_id = $this->getTypeformId($url_typeform);
        }
        $this->curl = new Curl();
    }

    /**
     * @param $token
     * @return array
     * @throws \Exception
     */
    public function getForm($token)
    {
        $params = [
            'key'   => $this->getApiKey(),
            'token' => $token
        ];

        $data = $this->getHttpResponse($params);

        return [
            'stats'     => $data['stats']['responses'],
            'questions' => $data['questions'],
            'responses' => $data['responses'][0]
        ];

    }

    /**
     * @return $this|mixed
     * @throws \Exception
     */
    public function getForms()
    {
        // Calculate offset
        $offset = $this->getPage() * $this->getLimit();
        $params = [
            'key'       => $this->getApiKey(),
            'offset'    => $offset,
            'limit'     => $this->getLimit(),
            'completed' => $this->getCompleted()
        ];

        if ($this->getSince()) {
            $params['since'] = $this->getSince();
        }
        if ($this->getUntil()) {
            $params['until'] = $this->getUntil();
        }

        $data = $this->getHttpResponse($params);

        $this->initializeForm($data);

        return $this;
    }

    /**
     *
     */
    public function getAllForms()
    {

        $this->resetResults();

        $previous_limit = $this->getLimit();
        $this->setLimit(self::MAX_LIMIT_RANGE);

        $this->getForms();
        $total = ($this->getCompleted()) ? $this->stats['responses']['completed'] : $this->stats['responses']['total'];

        while (count($this->getResponses()) < $total) {
            $this->getNextPage();
        }

        $this->setLimit($previous_limit);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getPayLoad()
    {
        $request_body = file_get_contents('php://input');
        $data = json_decode($request_body, true);

        if (is_null($data) || !isset($data['form_response'])) {
            throw new \Exception('Get payload fails.');
        }


        return [
            'hiddens'   => isset($data['form_response']['hidden']) ? $data['form_response']['hidden'] : [],
            'score'     => isset($data['form_response']['calculated']) ? $data['form_response']['calculated']['score'] : 0,
            'token'     => $data['form_response']['token'],
            'questions' => isset($data['form_response']['definition']['fields']) ? $data['form_response']['definition']['fields'] : [],
            'answers'   => isset($data['form_response']['answers']) ? $data['form_response']['answers'] : [],
        ];
    }


    /**
     * @return array
     */
    public function formatDataByExport()
    {
        return [
            'headers' => $this->getHeaders(),
            'rows'    => $this->getRows()
        ];
    }


    /**
     * @return $this|Typeform|mixed
     * @throws \Exception
     */
    public function getNextPage()
    {
        $this->nextPage();
        return $this->getForms();
    }

    /**
     * @return $this|Typeform|mixed
     * @throws \Exception
     */
    public function getPrevPage()
    {
        $this->prevPage();
        return $this->getForms();
    }


    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->api_key;
    }

    /**
     * @param $api_key
     * @return $this
     */
    public function setApiKey($api_key)
    {
        $this->api_key = $api_key;
        return $this;
    }

    /**
     * @return string
     */
    public function getApiUri()
    {
        return $this->api_uri;
    }

    /**
     * @param $api_uri
     * @return $this
     */
    public function setApiUri($api_uri)
    {
        $this->api_uri = $api_uri;
        return $this;
    }

    /**
     * @return int
     */
    public function getFormId()
    {
        return $this->typeform_id;
    }

    /**
     * @param $formId
     * @return $this
     */
    public function setFormId($formId)
    {
        $this->resetResults();
        $this->typeform_id = $formId;
        return $this;
    }

    /**
     * @param $url_typeform
     * @return $this
     */
    public function setFormIdFromUrl($url_typeform)
    {
        $this->resetResults();
        $this->typeform_id = $this->getTypeformId($url_typeform);
        return $this;
    }

    /**
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param int $page
     * @return $this
     */
    public function setPage($page)
    {
        if ($page < 0) {
            $this->page = 0;
        } else {
            $this->page = $page;
        }
        return $this;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param $limit
     * @return $this
     */
    public function setLimit($limit)
    {
        if ($limit > self::MAX_LIMIT_RANGE) {
            throw new \InvalidArgumentException("Pagination defaults are 0 and 1000 responses/page (" . self::MAX_LIMIT_RANGE . " is the maximum allowed).");
        }
        $this->limit = $limit;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getQuestions()
    {
        return $this->questions;
    }

    /**
     * @param $questions
     * @return $this
     */
    protected function setQuestions($questions)
    {
        $this->questions = array_merge($this->questions, $questions);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getResponses()
    {
        return $this->responses;
    }

    /**
     * @param $responses
     * @return $this
     */
    protected function setResponses($responses)
    {
        $this->responses = array_merge($this->responses, $responses);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStats()
    {
        return $this->stats;
    }

    /**
     * @param mixed $stats
     * @return Typeform
     */
    protected function setStats($stats)
    {
        $this->stats = $stats;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCompleted()
    {
        return var_export($this->completed, true);
    }

    /**
     * @param $completed
     * @return $this
     */
    public function setCompleted($completed)
    {
        $this->completed = $completed;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSince()
    {
        return $this->since;
    }

    /**
     * @param $since
     * @return $this
     */
    public function setSince($since)
    {
        $this->since = $since;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUntil()
    {
        return $this->until;
    }

    /**
     * @param $until
     * @return $this
     */
    public function setUntil($until)
    {
        $this->until = $until;
        return $this;
    }

    public function getUrlApi()
    {
        return $this->api_uri . $this->typeform_id;
    }

    /**
     * @return Typeform
     */
    protected function nextPage()
    {
        $page = $this->getPage();
        return $this->setPage(++$page);
    }

    /**
     * @return Typeform
     */
    protected function prevPage()
    {
        $page = $this->getPage();
        return $this->setPage(--$page);
    }

    /**
     * @param $data
     */
    protected function initializeForm($data)
    {
        $questionArray = [];
        foreach ($data['questions'] as $question) {
            $questionArray[$question['id']] = $question['question'];
        }
        $this->setQuestions($questionArray);
        $this->setResponses($data['responses']);
        $this->setStats($data['stats']);
    }

    /**
     * @param $typeform_url
     * @return mixed
     */
    private function getTypeformId($typeform_url)
    {
        $re = "@/to/([^?]+)@";
        preg_match($re, $typeform_url, $matches);
        return $matches[1];
    }

    /**
     * @param $params
     * @return mixed
     * @throws \Exception
     */
    protected function getHttpResponse($params)
    {
        $this->curl->get($this->getUrlApi(), $params);

        if ($this->curl->error) {
            throw new \Exception('The supplied API key is not valid');
        }

        $data = json_decode($this->curl->rawResponse, true);

        if (is_null($data)) {
            throw new \Exception('The supplied API key is not valid');
        }

        return $data;

    }

    /**
     * @return array
     */
    protected function getHeaders()
    {
        $headers = [];
        foreach ($this->getQuestions() as $id => $question) {
            $headers[$id] = $question;
        }
        $headers['score'] = 'score';

        return array_map('utf8_decode', $headers);
    }

    /**
     * @return array
     */
    protected function getRows()
    {
        $rows = [];
        foreach ($this->getResponses() as $response) {

            $fields = [];
            foreach ($this->getQuestions() as $question_id => $question_name) {
                $rr = '';
                if (isset($response['answers'][$question_id])) {
                    $rr = $response['answers'][$question_id];
                } elseif (isset($response['hidden'][$question_name])) {
                    $rr = $response['hidden'][$question_name];
                }

                $fields[$question_id] = $rr;
            }

            $fields['score'] = isset($response['answers']['score']) ? $response['answers']['score'] : 0;
            //$rows[] = array_map('utf8_decode', $fields);
            $rows[] = $fields;
        }

        return $rows;
    }

    /**
     *
     */
    public function resetResults()
    {
        $this->responses = [];
        $this->questions = [];
        $this->stats = [];
    }
}