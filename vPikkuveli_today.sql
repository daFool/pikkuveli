/**
 * vPikkuveli_today
 * 
 * A daily "raw" view for current day. Requires vPikkuveli_main view to function
 * 
 * @category	Integration
 * @package		Pikkuveli
 * @author		Mauri "daFool" Sahlberg <mauri.sahlberg@gmail.com>
 * @copyright	2020 Mauri Sahlberg, Helsinki
 * @license		BSD-2 https://opensource.org/licenses/BSD-2-Clause
 * @link		https://github.com/daFool/pikkuveli
 *
 * @uses    vPikkuveli_main
 */
drop view if exists vPikkuveli_today;
create view vPikkuveli_today as (
    select title as otsikko, alkoi, loppui, kesti, 
        sum(kesti) over (partition by title) as yhteensa, 
        sum(kesti) over (partition by alkoi::date) as paiva_yhteensa,
        kategoriat, kategoriaidt  
    from vPikkuveli_main where alkoi::date = now()::date order by alkoi asc
);

comment on view vPikkuveli_today is 'Daily activity view';
comment on column vPikkuveli_today.otsikko  is 'Entry title';
comment on column vPikkuveli_today.alkoi is 'Edit starttime';
comment on column vPikkuveli_today.loppui is 'Edit ended';
comment on column vPikkuveli_today.kesti is 'Edit duration';
comment on column vPikkuveli_today.yhteensa is 'Day total for this title';
comment on column vPikkuveli_today.kategoriat is 'Related categorynames - an array';
comment on column vPikkuveli_today.kategoriaidt is 'Related category id - an array';

