<?php

namespace App\Objects;

class BDMScholzModel{

$CONFRONTATION = 'confrontation';
$COMPROMISE = 'compromise';
$CAPITULATION = 'capitulation';
$OFFER_TYPES = (
CONFRONTATION,
 COMPROMISE,
 CAPITULATION,
);


function __construct($data, $q = 1.0) {

$this->q = $q;
$positions = $this->positions();
$this->position_range = max($positions) - min($positions);
}

function actors() {

Actor(name = item['Actor'],
 c = float(item['Capability']),
 s = float(item['Salience']),
 x = float(item['Position']),
 model = self)
for item in data];

foreach ($data as $actor) {

$actor
}
}


function cvsPath($cvsPath) {

return cls(csv . DictReader(open(csv_path, 'rU')));
}

function positions() {
$a = [];
foreach ($this->actors as $actor) {
array_push($a, $actor->x);
}
return $a;
}

function mediaPosition() {
$positions = $this->positions();
$media = $positions[0];
foreach ($positions as $position) {

$sum = 0;
foreach ($this->actors as $actor) {
$sum += $actor->compare($position, $median, $risk = 1.0 );
}
if ($sum>0){

$median = $position;
}
return $median;
}
}

function meanPosition() {
$sum = 0;
foreach ($this->actors as $actor) {
$sum += $actor->c * $actor->s * $actor->x;
}

$sum1 = 0;
foreach ($this->actors as $actor) {
$sum1+= $actor->c * $actor->s);
}
return $sum/$sum1;
}

function probability($x_i, $x_j) {

if ($x_i == $x_j){
return 0.0;
}
foreach ($this->actors as $actor) {
$sum1+= $actor->c * $actor->s);
}
$sum_all_votes = sum(abs($actor.compare(a1.x, a2.x));
for actor in self.actors
for a1 in self.actors
for a2 in self.actors)
return (sum(max(0, actor.compare(x_i, x_j)) for actor in self.actors) /
sum_all_votes)


}

function updateRisk() {
foreach ($this->actors as $actor){
    $actor->r = 1.0
}

actor_to_risk_aversion = [(actor, actor.risk_aversion())
                                  for actor in self.actors]
}

}
