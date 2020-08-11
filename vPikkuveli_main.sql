/**
 * vPikkuveli_main
 *  
 * Pikkuveli ("little brother") time tracking application for Serendipity
 * 
 * @category	DatabaseView
 * @package	Pikkuveli
 * @author	Mauri "daFool" Sahlberg <mauri.sahlberg@gmail.com>
 * @copyright	2020 Mauri Sahlberg, Helsinki
 * @license	BSD-2 https://opensource.org/licenses/BSD-2-Clause
 * @link	https://github.com/daFool/pikkuveli
 *
 * NOTE: This uses arrays, will probably break in every other database than Postgresql!
 */
drop view if exists vPikkuveli_main;
create view vPikkuveli_main as (
select  e.title,
        replace(starts, '.',':')::timestamp without time zone as alkoi, 
        replace(ends, '.',':')::timestamp without time zone as loppui,
        interval '1 second'*seconds as kesti,
        comment,
        array(select c.category_name from
                serendipity_entrycat as ec
        join
                serendipity_category as c
        on (ec.categoryid=c.categoryid and ec.entryid=st.entry_id)) as kategoriat,
        array(select c.categoryid from
                serendipity_entrycat as ec
        join
                serendipity_category as c
        on (ec.categoryid=c.categoryid and ec.entryid=st.entry_id)) as kategoriaidt
from 
        serendipity_pikkuveli_stamps as st
join
        serendipity_entries as e
on (st.entry_id = e.id)
);

comment on view vPikkuveli_main is 'Cooked versions of timestamps, probably requires a database software';
comment on column vPikkuveli_main.title is 'Title of the entry';
comment on column vPikkuveli_main.alkoi is 'Edit started';
comment on column vPikkuveli_main.loppui is 'Edit ended';
comment on column vPikkuveli_main.kesti is 'Duration of edit';
comment on column vPikkuveli_main.kategoriat is 'Array of category names';
comment on column vPikkuveli_main.kategoriaidt is 'Array of category ids';
