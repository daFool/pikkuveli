CREATE OR REPLACE VIEW PIKKUVELI_STAMPS AS
	SELECT entry_id as id,
		(''||replace(starts,'.',':')||'')::timestamp as starts,
		(''||replace(ends,'.',':')||'')::timestamp as ends,
		(seconds||' seconds')::interval as kesto,
	   ((seconds||' seconds')::interval)::text as kesto_a,
	   extract(epoch from ((seconds||' seconds')::interval)) as kesto_s,
		comment 
	FROM serendipity_pikkuveli_stamps;

CREATE OR REPLACE VIEW DUUNIT AS 
  SELECT l.*, e.title, e.author,c.category_name FROM PIKKUVELI_STAMPS AS l 
    JOIN serendipity_entries AS e ON(l.id=e.id) 
    JOIN serendipity_entrycat AS ec ON (e.id=ec.entryid) 
    JOIN serendipity_category AS c ON(ec.categoryid=c.categoryid);
