import argparse
from collections import defaultdict
import csv
import sys


class Actor(object):
    def __init__(self, name, c, s, x, model, r=1.0):
        self.name = name
        self.c = c  # capabilities, float between 0 and 1
        self.s = s  # salience, float between 0 and 1
        self.x = x  # number representing position on an issue
        self.model = model
        self.r = r  # risk aversion, float between .5 and 2

    def __str__(self):
        return self.__repr__()

    def __repr__(self):
        return '%s(x=%s,c=%s,s=%s,r=%.2f)' % (
            self.name, self.x, self.c, self.s, self.r)

    def compare(self, x_j, x_k, risk=None):
        risk = risk or self.r

        position_range = self.model.position_range
        x_k_distance = (abs(self.x - x_k) / position_range) ** risk
        x_j_distance = (abs(self.x - x_j) / position_range) ** risk
        return self.c * self.s * (x_k_distance - x_j_distance)

    def u_success(self, actor, x_j):
        position_range = self.model.position_range
        val = 0.5 - 0.5 * abs(actor.x - x_j) / position_range
        return 2 - 4 * val ** self.r

    def u_failure(self, actor, x_j):
        position_range = self.model.position_range
        val = 0.5 + 0.5 * abs(actor.x - x_j) / position_range
        return 2 - 4 * val ** self.r

    def u_status_quo(self):
        return 2 - 4 * (0.5 ** self.r)

    def eu_challenge(self, actor_i, actor_j):
        prob_success = self.model.probability(actor_i.x, actor_j.x)
        u_success = self.u_success(actor_i, actor_j.x)
        u_failure = self.u_failure(actor_i, actor_j.x)
        u_status_quo = self.u_status_quo()

        eu_resist = actor_j.s * (
            prob_success * u_success + (1 - prob_success) * u_failure)
        eu_not_resist = (1 - actor_j.s) * u_success
        eu_status_quo = self.model.q * u_status_quo

        return eu_resist + eu_not_resist - eu_status_quo

    def danger_level(self):
        return sum(self.eu_challenge(other_actor, self) for other_actor
                   in self.model.actors if other_actor != self)

    def risk_acceptance(self):

        danger_levels = [actor.danger_level() for actor in self.model.actors]
        max_danger = max(danger_levels)
        min_danger = min(danger_levels)
        return ((2 * self.danger_level() - max_danger - min_danger) /
                (max_danger - min_danger))

    def risk_aversion(self):
        risk = self.risk_acceptance()
        return (1 - risk / 3.0) / (1 + risk / 3.0)

    def best_offer(self):
        offers = defaultdict(list)

        for other_actor in self.model.actors:
            if self.x == other_actor.x:
                continue

            offer = Offer.from_actors(self, other_actor)
            if offer:
                offers[offer.offer_type].append(offer)

        best_offer = None
        best_offer_key = lambda offer: abs(self.x - offer.position)
        def compromise_best_offer_key(offer):
            top = (abs(offer.eu) * offer.actor.x +
                   abs(offer.other_eu) * offer.other_actor.x)
            return top / (abs(offer.eu) + abs(offer.other_eu))

        if offers['confrontation']:
            best_offer = min(offers['confrontation'], key=best_offer_key)
        elif offers['compromise']:
            best_offer = min(offers['compromise'],
                             key=compromise_best_offer_key)
        elif offers['capitulation']:
            best_offer = min(offers['capitulation'], key=best_offer_key)

        return best_offer


class Offer(object):
    CONFRONTATION = 'confrontation'
    COMPROMISE = 'compromise'
    CAPITULATION = 'capitulation'
    OFFER_TYPES = (
        CONFRONTATION,
        COMPROMISE,
        CAPITULATION,
    )

    def __init__(self, actor, other_actor, offer_type, eu, other_eu, position):
        if offer_type not in self.OFFER_TYPES:
            raise ValueError('offer_type "%s" not in %s'
                             % (offer_type, self.OFFER_TYPES))

        self.actor = actor  # actor receiving the offer
        self.other_actor = other_actor  # actor proposing the offer
        self.offer_type = offer_type
        self.eu = eu
        self.other_eu = other_eu
        self.position = position

    @classmethod
    def from_actors(cls, actor, other_actor):
        eu_ij = actor.eu_challenge(actor, other_actor)
        eu_ji = actor.eu_challenge(other_actor, actor)

        if eu_ji > eu_ij > 0:
            offer_type = cls.CONFRONTATION
            position = other_actor.x
        elif eu_ji > 0 > eu_ij and eu_ji > abs(eu_ij):
            offer_type = cls.COMPROMISE
            concession = (other_actor.x - actor.x) * abs(eu_ij / eu_ji)
            position = actor.x + concession
        elif eu_ji > 0 > eu_ij and eu_ji < abs(eu_ji):
            offer_type = cls.CAPITULATION
            position = other_actor.x
        else:
            return None

        return cls(actor, other_actor, offer_type, eu_ij, eu_ji, position)

    def __str__(self):
        return self.__repr__()

    def __repr__(self):
        type_to_fmt = {
            self.CONFRONTATION: '%s loses confrontation to %s',
            self.COMPROMISE: '%s compromises with %s',
            self.CAPITULATION: '%s capitulates to %s',
        }
        fmt = type_to_fmt[self.offer_type] + "\n\t%s vs %s\n\tnew_pos = %s"

        return fmt % (self.actor.name, self.other_actor.name, self.eu,
                      self.other_eu, self.position)


class BDMScholzModel(object):

    def __init__(self, data, q=1.0):
        self.actors = [
            Actor(name=item['Actor'],
                  c=float(item['Capability']),
                  s=float(item['Salience']),
                  x=float(item['Position']),
                  model=self)
            for item in data]
        self.name_to_actor = {actor.name: actor for actor in self.actors}
        self.q = q
        positions = self.positions()
        self.position_range = max(positions) - min(positions)

    @classmethod
    def from_csv_path(cls, csv_path):
        return cls(csv.DictReader(open(csv_path, 'rU')))

    def actor_by_name(self, name):
        return self.name_to_actor.get(name)

    def __getitem__(self, key):
        return self.name_to_actor.get(key)

    def positions(self):
        return list({actor.x for actor in self.actors})

    def median_position(self):
        positions = self.positions()
        median = positions[0]
        for position in positions[1:]:
            votes = sum(actor.compare(position, median, risk=1.0)
                        for actor in self.actors)
            if votes > 0:
                median = position
        return median

    def mean_position(self):
        return (sum(actor.c * actor.s * actor.x for actor in self.actors) /
                sum(actor.c * actor.s for actor in self.actors))

    def probability(self, x_i, x_j):
        if x_i == x_j:
            return 0.0
        sum_all_votes = sum(abs(actor.compare(a1.x, a2.x))
                            for actor in self.actors
                            for a1 in self.actors
                            for a2 in self.actors)
        return (sum(max(0, actor.compare(x_i, x_j)) for actor in self.actors) /
                sum_all_votes)

    def update_risk_aversions(self):
        for actor in self.actors:
            actor.r = 1.0

        actor_to_risk_aversion = [(actor, actor.risk_aversion())
                                  for actor in self.actors]
        for actor, risk_aversion in actor_to_risk_aversion:
            actor.r = risk_aversion

    def update_positions(self):
        actor_to_best_offer = [(actor, actor.best_offer())
                               for actor in self.actors]
        for actor, best_offer in actor_to_best_offer:
            if best_offer:
                #comment by jucjimenezmo
                #print (best_offer)
                actor.x = best_offer.position

    def run_model(self, num_rounds=1):

        #orig_stdout = sys.stdout
        #f = open('out.txt', 'w')
        #sys.stdout = f

        # se pintan los nombres de todos los actores - jucjimenezm
        # actor1,actor2,actor3
        for i in range(0, len(self.actors)):
            if i + 1 == len(self.actors):
                print(self.actors[i].name)
            else:
                print(self.actors[i].name, end=',')

        """
        print ('Median position: %s' % self.median_position())
        print ('Mean position: %s' % self.mean_position())
        """
        for round_ in range(1, num_rounds + 1):
            self.update_risk_aversions()
            self.update_positions()

            """
                        print ('')
            print ('ROUND %d' % round_)

            print ('')
            print ('Median position: %s' % self.median_position())
            print ('Mean position: %s' % self.mean_position())
            print('julian')
            print (self.actors)

            """

            # se pintan los valores de las posiciones en esa ronda
            # 12, 14, 100
            for i in range(0, len(self.actors)):
                if i+1 == len(self.actors):
                    print(self.actors[i].x)
                else:
                    print(self.actors[i].x, end=',')

            #tests-jucjimenezmo
            #print(self.positions())
            #print(self.actors)

        # jcjimenezm
        #sys.stdout = orig_stdout
        #f.close()


if __name__ == '__main__':
    parser = argparse.ArgumentParser()
    parser.add_argument(
        'csv_path',
        help='path to csv with input data')
    parser.add_argument(
        'num_rounds',
        help='number of rounds of simulation to run',
        type=int)
    args = parser.parse_args()

    model = BDMScholzModel.from_csv_path(args.csv_path)
    model.run_model(num_rounds=args.num_rounds)
