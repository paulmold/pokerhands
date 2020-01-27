<?php

namespace App\Http\Controllers;

use App\Hand;
use App\Round;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    const NUMBER_ORDER = ['A', 'K', 'Q', 'J', 'T', '9', '8', '7', '6', '5', '4', '3', '2'];

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $rounds = Round::where('user_id', Auth::id())->get();

        return view('home', [
            'rounds' => $rounds
        ]);
    }

    /**
     * Show round
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function round(int $id)
    {
        $rounds = Hand::where('round_id', $id)->get();
        $hand1wins = Hand::where('round_id', $id)->where('result', 1)->count();

        return view('round', [
            'rounds' => $rounds,
            'hand1wins' => $hand1wins
        ]);
    }

    /**
     * Show the upload form
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function upload()
    {
        return view('upload');
    }

    /**
     * Upload file and redirect to home
     *
     * @param Request $request
     */
    public function uploadFile(Request $request)
    {
        if (!$file = $request->file('file')) {
            return view('upload', [
                'error' => 'No file has been selected'
            ]);
        }

        if (!$file->isValid()) {
            return view('upload', [
                'error' => 'Error while uploading'
            ]);
        }

        if ($file->extension() != 'txt') {
            return view('upload', [
                'error' => 'File needs to be .txt'
            ]);
        }

        $roundsFile = fopen($file->getRealPath(), "r");
        while (!feof($roundsFile)) {
            $rounds[] = fgets($roundsFile);
        }

        fclose($roundsFile);

        if (isset($rounds)) {
            $roundModel =  new Round();
            $roundModel->name = $request->input('name', 'no name');
            $roundModel->user_id = Auth::id();
            $roundModel->save();

            foreach ($rounds as $round) {
                $hand1 = substr($round, 0, 14);
                $hand2 = substr($round, 15, 14);

                if ((strlen($hand1) == 14) && (strlen($hand2) == 14)) {
                    $compiledHand1 = $this->checkHand($hand1);
                    $compiledHand2 = $this->checkHand($hand2);
                    $handsComparison = $this->compareHands($compiledHand1, $compiledHand2);

                    $handModel = new Hand();
                    $handModel->hand1_cards = implode(' ', $compiledHand1['cards']);
                    $handModel->hand2_cards = implode(' ', $compiledHand2['cards']);
                    $handModel->hand1 = $compiledHand1['hand'];
                    $handModel->hand2 = $compiledHand2['hand'];
                    $handModel->result = $handsComparison;
                    $handModel->round_id = $roundModel->id;
                    $handModel->save();
                }
            }
        }

        return redirect('home');
    }

    /**
     * @param array $hand1
     * @param array $hand2
     * @return int
     */
    private function compareHands(array $hand1, array $hand2)
    {
        switch ($hand1['order'] <=> $hand2['order']) {
            case -1:
                return 1;
                break;
            case 1:
                return 2;
                break;
            case 0:
                $i = 0;
                $result = 0;
                while (isset($hand1['numbers'][$i]) && ($result == 0)) {
                    switch ($hand1['numbers'][$i] <=> $hand2['numbers'][$i]) {
                        case -1:
                            $result = 1;
                            break;
                        case 1:
                            $result = 2;
                            break;
                    }
                    $i++;
                }
                return $result;
                break;
        }
    }

    /**
     * Get hand from string
     *
     * @param string $hand
     * @return array
     */
    private function checkHand(string $hand)
    {
        $checkStraight = false;
        $checkFlush = false;
        $checkFullHouse = false;
        $checkFourOfAKind = false;
        $checkThreeOfAKind = false;
        $checkTwoPairs = false;
        $checkOnePair = false;
        $highestNumbers = [];

        $cards = explode(' ', $hand);
        usort($cards, function ($a, $b) {
            $pos_a = array_search($a[0], self::NUMBER_ORDER);
            $pos_b = array_search($b[0], self::NUMBER_ORDER);
            return $pos_a - $pos_b;
        });

        foreach ($cards as $key => $card) {
            $numbers[] = $card[0];
            $colors[] = $card[1];
        }

        $countNumbers = array_count_values($numbers);
        foreach ($countNumbers as $key => $value) {
            if ($value == 4) {
                $checkFourOfAKind = true;
                array_unshift($highestNumbers, $key);
            } else if ($value == 3) {
                if ($checkOnePair == true) {
                    $checkFullHouse = true;
                    $checkOnePair = false;
                } else {
                    $checkThreeOfAKind = true;
                }
                array_unshift($highestNumbers, $key);
            } else if ($value == 2) {
                if ($checkOnePair == true) {
                    $checkTwoPairs = true;
                    $checkOnePair = false;
                    if ($key > $highestNumbers[0]) {
                        array_unshift($highestNumbers, $key);
                    } else {
                        if (isset($highestNumbers[1])) {
                            array_splice($highestNumbers, 1, 0, $key);
                        } else {
                            $highestNumbers[] = $key;
                        }
                    }
                } else {
                    if ($checkThreeOfAKind == true) {
                        $checkFullHouse = true;
                        $checkThreeOfAKind = false;
                        $highestNumbers[] = $key;
                    } else {
                        $checkOnePair = true;
                        array_unshift($highestNumbers, $key);
                    }
                }
            } else {
                $highestNumbers[] = $key;
            }
        }

        if (!$checkOnePair && !$checkTwoPairs && !$checkThreeOfAKind && !$checkFourOfAKind && !$checkFullHouse) {
            $countColors = array_count_values($colors);
            if (array_pop($countColors) == 5) {
                $checkFlush = true;
            }

            $straightHands = ['A5432', '65432', '76543', '87654', '98765', 'T9876', 'JT987', 'QJT98', 'KQJT9', 'AKQJT'];
            $numbersHand = implode('', $numbers);
            if (in_array($numbersHand, $straightHands)) {
                $checkStraight = true;
            }
        }

        if ($checkStraight && $checkFlush) {
            if ($highestNumbers[1] == 'K') {
                return [
                    'order' => 1,
                    'hand' => 'Royal flush',
                    'numbers' => $highestNumbers,
                    'cards' => $cards
                ];
            }

            return [
                'order' => 2,
                'hand' => 'Straight flush',
                'numbers' => $highestNumbers,
                'cards' => $cards
            ];
        }

        if ($checkFourOfAKind) {
            return [
                'order' => 3,
                'hand' => 'Four of a kind',
                'numbers' => $highestNumbers,
                'cards' => $cards
            ];
        }

        if ($checkFullHouse) {
            return [
                'order' => 4,
                'hand' => 'Full house',
                'numbers' => $highestNumbers,
                'cards' => $cards
            ];
        }

        if ($checkFlush) {
            return [
                'order' => 5,
                'hand' => 'Flush',
                'numbers' => $highestNumbers,
                'cards' => $cards
            ];
        }

        if ($checkStraight) {
            return [
                'order' => 6,
                'hand' => 'Straight',
                'numbers' => $highestNumbers,
                'cards' => $cards
            ];
        }

        if ($checkThreeOfAKind) {
            return [
                'order' => 7,
                'hand' => 'Three of a kind',
                'numbers' => $highestNumbers,
                'cards' => $cards
            ];
        }

        if ($checkTwoPairs) {
            return [
                'order' => 8,
                'hand' => 'Two pairs',
                'numbers' => $highestNumbers,
                'cards' => $cards
            ];
        }

        if ($checkOnePair) {
            return [
                'order' => 9,
                'hand' => 'One pair',
                'numbers' => $highestNumbers,
                'cards' => $cards
            ];
        }

        return [
            'order' => 10,
            'hand' => 'High card',
            'numbers' => $highestNumbers,
            'cards' => $cards
        ];
    }
}
