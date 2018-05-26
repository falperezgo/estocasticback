<?php

namespace App\Objects\Actor;

use Illuminate\Http\Request;
use App\Http\Requests;

class Actor {

    public function __construct() {
        $c = 0;  #capabilities, float between 0 y 1;        
        $s = 0;   #salience
        $x = 0; #position on an issue

        $model = "";
        $r = 0; #risk aversion
    }

    public function printo($param) {
        return 'x=' . s . '  c=' . c . ' s=' . s . ' r=' . r;
    }

    public function compare($x_k, $x_j, $risk = null) {

        $risk = $position_range = $this->model . position_range;
        $x_k_distance = (abs($this->x - $x_k) / position_range) * $risk;
        $x_j_distance = (abs($this->x - $x_j) / position_range) * $risk;
        return $this->c * $this->s * (x_k_distance - x_j_distance);
    }

    public function success($param) {

        $position_range = $this->model . position_range;
        $val = 0.5 - 0.5 * abs(actor . x - x_j) / position_range;
        return 2 - 4 * pow($val, $this->r);
    }

    public function failure($param) {

        $position_range = $this->model . position_range;
        $val = 0.5 - 0.5 * abs(actor . x - x_j) / position_range;
        return 2 - 4 * pow($val, $this->r);
    }

    public function statusQuo() {
        return 2 - 4 * pow(0.5, $this->r);
    }

    public function challengue($actor_i, $actor_j) {

        $prob_success = $this->model . probability($actor_i . x, $actor_j . x);
        $u_success = $this->success($actor_i, $actor_j . x);
        $u_failure = $this->failure(actor_i, actor_j . x);
        $u_status_quo = $this->statusQuo();

        $eu_resist = $actor_j . s * ($prob_success * $u_success + (1 - $prob_success) * $u_failure);
        $eu_not_resist = (1 - $actor_j . s) * $u_success;
        $eu_status_quo = $this->model . q * $u_status_quo;

        return $eu_resist + $eu_not_resist - $eu_status_quo;
    }

    public function dangerLevel() {

        foreach ($this->model->actors as $otheractor) {

            if ($otheractor != $this) {
                sum($this->challenge(other_actor, self);
            }
        }

//        return sum($this->challenge(other_actor, self) for other_actor in self.model.actors if other_actor != self)
    }

    public function riskAceptance() {

        foreach ($this->model->actors as $actor) {
            $actor->challenge(other_actor, self);
        }
//        $danger_levels = [actor.danger_level() for actor in self.model.actors]
        $max_danger = max($danger_levels);
        $min_danger = min($danger_levels);
        return ((2 * $this->danger_level() - $max_danger - $min_danger) / ($max_danger - $min_danger));
    }

    public function riskAversion() {
        $risk = $this->riskAcceptance();
        return (1 - $risk / 3.0) / (1 + $risk / 3.0);
    }

    public function bestOffer($param) {

        $offers = defaultdict(list);

        foreach ($this->model->actors as $otheractor) {
            if ($this->x == $otheractor->x) {
                $offer = Offer . from_actors(self, other_actor);
            }
        }



        for other_actor in self.model.actors:
        if self.x == other_actor.x:
        continue

        offer = Offer.from_actors(self, other_actor)
        if offer:
        offers[offer.offer_type].append(offer)

        best_offer = None
        best_offer_key = lambda offer: abs(self.x - offer.position)
    }

    public function compromiseBestOffer($offer) {
        $top = (abs($offer->eu) * $offer->actor->x + abs($offer->other_eu) * $offer->other_actor->x);
        return $top / (abs($offer->eu) + abs($offer->other_eu));

        if ($offers['confrontation']) {
            $best_offer = min($offers['confrontation'], $key = $best_offer_key);
        } elseif ($offers['compromise']) {
            $best_offer = min($offers['compromise'],$key = $compromise_best_offer_key);
        } elseif ($offers['capitulation']) {
             $best_offer = min($offers['capitulation'], $key = $best_offer_key);
        }

        return $best_offer;
    }

}
