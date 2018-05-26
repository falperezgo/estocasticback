<?php

namespace App\Objects;

class Offer{

$CONFRONTATION = 'confrontation';
$COMPROMISE = 'compromise';
$CAPITULATION = 'capitulation';
$OFFER_TYPES = (
CONFRONTATION,
 COMPROMISE,
 CAPITULATION,
);


public function __construct($actor, $other_actor, $offer_type, $eu, $other_eu, $position) {


if offer_type not in self.OFFER_TYPES:
raise ValueError('offer_type "%s" not in %s'
% (offer_type, self.OFFER_TYPES))

$this->actor = $actor;  # actor receiving the offer
$this->other_actor = $other_actor;  # actor proposing the offer
$this->offer_type = $offer_type;
$this->eu = $eu;
$this->other_eu = $other_eu;
$this->position = $position;
}

function fromActors($cls, $actor, $other_actor) {
$eu_ij = $actor->challenge($actor, $other_actor);
$eu_ji = $actor->challenge($other_actor, $actor);

if ($eu_ji > $eu_ij > 0){
$offer_type = $cls.CONFRONTATION$param;
$position = $other_actor->x;
}elseif($eu_ji > 0 > $eu_ij && $eu_ji > abs($eu_ij)){
$offer_type = cls.COMPROMISE;
$concession = ($other_actor->x - $actor->x) * abs($eu_ij / $eu_ji);
$position = $actor->x + $concession
}

function repr($param) {

type_to_fmt = {
$this->CONFRONTATION: '%s loses confrontation to %s',
 $this->COMPROMISE: '%s compromises with %s',
 $this->CAPITULATION: '%s capitulates to %s',
 }
$fmt = type_to_fmt[$this->offer_type] + "\n\t%s vs %s\n\tnew_pos = %s";

return fmt % ($this->actor->name, $this->other_actor->name, $this->eu, $this->other_eu, $this->position);

}


}

}
