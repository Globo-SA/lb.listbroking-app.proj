SELECT
  l0_.id AS id0,
  c1_.id AS id1
FROM lead l0_ LEFT JOIN lb_lock l2_ ON l0_.id = l2_.lead_id AND ((l2_.expiration_date >= '2015-11-12 17:57:55') AND
                                                                 ((l2_.type = 4 AND l2_.campaign_id = '5' AND (l2_.lock_date >= '2015-05-12 17:57:55')) OR
                                                                  (l2_.type = 3 AND l2_.client_id = '3' AND (l2_.lock_date >= '2015-05-12 17:57:55'))))
  INNER JOIN contact c1_
    ON l0_.id = c1_.lead_id AND (c1_.firstname IS NOT NULL AND c1_.postalcode1 IS NOT NULL AND (c1_.date BETWEEN '2012/01/01' AND '2015/04/30') AND c1_.is_clean IN (1) AND c1_.country_id IN ('1'))
WHERE l2_.lead_id IS NULL AND l0_.in_opposition IN (0)
GROUP BY l0_.id
LIMIT 5000