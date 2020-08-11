/**
 * vPikkuveli_week
 * 
 * A weekly view for the current week. Requires vPikkuveli_week_raw view to function
 * 
 * @category	Integration
 * @package	Pikkuveli
 * @author	Mauri "daFool" Sahlberg <mauri.sahlberg@gmail.com>
 * @copyright	2020 Mauri Sahlberg, Helsinki
 * @license	BSD-2 https://opensource.org/licenses/BSD-2-Clause
 * @link	https://github.com/daFool/pikkuveli
 *
 * @uses    vPikkuveli_week_raw
 */

drop view if exists vPikkuveli_week;
create view vPikkuveli_week as (
select viikko as viikkonumero, viikonpaiva, title, alkoi, loppui, kesti, 
        sum(kesti) over(partition by title) as tehtava, 
        sum(kesti) over(partition by viikonpaiva) as paiva, 
        sum(kesti) over (partition by viikko) as viikko,
        comment,
        kategoriat,
        kategoriaidt
from vpikkuveli_week_raw order by viikonpaiva asc, alkoi asc
);
comment on view vPikkuveli_week is 'The current week of activity';
comment on column vPikkuveli_week.viikkonumero is 'Week number';
comment on column vPikkuveli_week.viikonpaiva is 'Dow - number, 1-Monday';
comment on column vPikkuveli_week.title is 'Entry title';
comment on column vPikkuveli_week.alkoi is 'Edit started';
comment on column vPikkuveli_week.loppui is 'Edit ended';
comment on column vPikkuveli_week.kesti is 'Edit duration';
comment on column vPikkuveli_week.paiva is 'Total hours on day';
comment on column vPikkuveli_week.viikko is 'Total hours for the week';
comment on column vPikkuveli_week.comment is 'Stamp comment';
comment on column vPikkuveli_week.kategoriat is 'Related categories names in an array';
comment on column vPikkuveli_week.kategoriaidt is 'Related categories ids in an array';