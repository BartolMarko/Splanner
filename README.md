# SPLANNER

Planer sportskih aktivnosti

## deploy.sh

Za korištenje deploya prvo u `Splanner/student_username.txt` upišite svoj student username. Ovaj file je u zapisan u `.gitignore` i git bi ga trebao ignorirati tako da ga ne možete pushati na repozitorij.
Zatim runnajte `chmod +x deploy.sh` da možete pokrenuti deploy.

Sada će se s komandom `./deploy.sh` svi fileovi iz lokalnog Splanner direktorija prenijeti u direktorij `~/public_html/rp2/Splanner` na studentu.
Ako na studentu u ovom direktoriju ima nekih "viška" fileova koji više ne postoje u trenutnom lokalnom Splanner direktoriju onda runnajte `./deploy.sh --delete` i sad će stanje na studentu biti <b>identično</b> lokanom stanju, to jest viška fileovi će biti izbrisani.
