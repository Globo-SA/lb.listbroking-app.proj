SELECT *
FROM lead l0_
LEFT JOIN lb_lock l1_  ON l0_.id = l1_.lead_id AND (l1_.type = 0 AND l1_.expiration_date >= CURRENT_TIMESTAMP)
WHERE l1_.id IS NULL
AND l0_.is_ready_to_use = 0
LIMIT 1