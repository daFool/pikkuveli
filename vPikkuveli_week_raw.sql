/**
 * vPikkuveli_week_raw
 * 
 * A weekly "raw" view for week. Requires vPikkuveli_main view to function
 * 
 * @category	Integration
 * @package	Pikkuveli
 * @author	Mauri "daFool" Sahlberg <mauri.sahlberg@gmail.com>
 * @copyright	2020 Mauri Sahlberg, Helsinki
 * @license	BSD-2 https://opensource.org/licenses/BSD-2-Clause
 * @link	https://github.com/daFool/pikkuveli
 *
 * @uses    vPikkuveli_main
 */
drop view if exists vPikkuveli_week_raw;
create view vPikkuveli_week_raw as (
        select  date_part('week',alkoi)::int as viikko, 
                date_part('dow',alkoi)::int as viikonpaiva, * 
        from vPikkuveli_main 
        where date_part('week', alkoi)=date_part('week',now()) and date_part('year', alkoi)=date_part('year', now())
);

comment on view vPikkuveli_week_raw is 'Weekly view on ongoing tasks';
comment on column vPikkuveli_week_raw.viikko is 'Week number';
comment on column vPikkuveli_week_raw.viikonpaiva is 'Weekday number 1-Monday, 2-Tuesday...';
comment on column vPikkuveli_week_raw.title is 'Title of the entry';
comment on column vPikkuveli_week_raw.alkoi is 'Edit started';
comment on column vPikkuveli_week_raw.loppui is 'Edit ended';
comment on column vPikkuveli_week_raw.kesti is 'Duration of edit';
comment on column vPikkuveli_week_raw.kategoriat is 'Array of category names';
comment on column vPikkuveli_week_raw.kategoriaidt is 'Array of category ids';
