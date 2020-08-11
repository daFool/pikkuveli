select otsikko, sum(yhteensa) from vPikkuveli_today group by otsikko;

select viikkonumero, viikonpaiva, title, sum(kesti) as kesto, 
        round(
          (
            (extract('epoch' from sum(kesti)) / extract('epoch' from min(paiva))*100)::numeric
          ),0
        ) as osuuspaivasta,
        round(
          (
            (extract('epoch' from sum(kesti)) / extract('epoch' from min(tehtava))*100)::numeric
          ),0
        ) as osuustehtavasta,
         round(
          (
            (extract('epoch' from sum(kesti)) / extract('epoch' from min(viikko))*100)::numeric
          ),0
        ) as osuusviikosta,
        
min(paiva) as paiva, min(tehtava) as tehtava, min(viikko) as viikko from vPikkuveli_week group by viikkonumero, viikonpaiva, title