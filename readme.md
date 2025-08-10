# 🤖 Chatbot s embeddings pro produkty, práci a kontakty

[![License](https://img.shields.io/badge/license-MIT-informational)](#)
[![Built with Python](https://img.shields.io/badge/Python-3.10%2B-blue)](#)
[![Embeddings](https://img.shields.io/badge/Embeddings-Enabled-success)](#)

Inteligentní chatbot, který načte data z DB, převede texty do vektorů (embedding) a na základě **similarity** vrací nejrelevantnější odpověď. Napojený na produktové tabulky, pracovní nabídky a kontakty. Běží jako skript spouštěný přes `bin/console` + **CRON**.

---

## 📚 Obsah
- [Princip](#-princip)
- [Funkce chatbota](#-funkce-chatbota)
- [Pipeline & aktualizace](#-pipeline--aktualizace)
- [Ukázky UI](#-ukázky-ui)
- [Vyhledávání produktů](#-vyhledávání-produktů)
- [Weights / váhy polí](#-weights--váhy-polí)
- [Demo video](#-demo-video)
- [Poznámky k nasazení](#-poznámky-k-nasazení)

---

## 🧠 Princip
- Z databáze se načtou tabulky, se kterými má chatbot pracovat.
- **Embedding**: veškerá textová data se skriptem převedou do vektorů a uloží do DB.
- Při každém dotazu se vygeneruje vektor dotazu **realtime** a porovná se s uloženými vektory.
- Podle nejvyšší **similarity** chatbot vybere odpověď a vrátí ji v poli `response`.

Skript je nyní v **Pythonu** (může být přepsán do JS). Výhoda: běží mimo prohlížeč, bez nutnosti tokenizeru v klientu.

---

## ✨ Funkce chatbota
- Napojení na **produkty**, **pracovní nabídky** a **kontakty** (návrhy se zobrazují najednou po výběru varianty).
- Chatbot:
  - pozdraví uživatele,
  - komentuje jeho akce,
  - umožní **zvětšit / zmenšit** zobrazovací okno.

---

## 🔁 Pipeline & aktualizace
- Spouštění: `bin/console` (napojené na **CRON**).
- Zbývá rozhodnout **frekvenci aktualizací** (doporučení níže):
  - Produkty: každých 6–12 h (dle obrátkovosti katalogu).
  - Pracovní nabídky: 1× denně.
  - Kontakty: týdně nebo při změně.

> Pokud se mění jen část záznamů, zvaž **inkrementální re-embedding** (reindex jen změněných položek).

---

## 🖼 Ukázky UI

**Komentáře a návrhy:**

<table>
<tr>
<td><img src="./prezentace/koment_produkty.png" alt="Komentáře k produktům"></td>
<td><img src="./prezentace/koment_produkty_2.png" alt="Komentáře k produktům 2"></td>
</tr>
<tr>
<td><img src="./prezentace/koment_kontakty.png" alt="Komentáře ke kontaktům"></td>
<td><img src="./prezentace/koment_prace.png" alt="Komentáře k pracovním nabídkám"></td>
</tr>
</table>

**Zvětšení / zmenšení okna:**

<table>
<tr>
<td><img src="./prezentace/buttons.png" alt="Ovládací prvky"></td>
<td><img src="./prezentace/not_full.png" alt="Menší okno"></td>
</tr>
<tr>
<td><img src="./prezentace/buttons_2.png" alt="Ovládací prvky 2"></td>
<td><img src="./prezentace/full.png" alt="Plné okno"></td>
</tr>
</table>

---

## 🔎 Vyhledávání produktů
Vyhledávání běží nad **vektorovými poli** jednotlivých tabulek a dotazem od chatbota:

<p align="center">
  <img src="./prezentace/Momb_hled.png" alt="Vyhledávání podle similarity">
</p>

<p align="center">
  <img src="./prezentace/MOMB_okno.png" alt="Okno výsledků">
</p>

**Příklad:** dotaz → vektor → výběr top-N podobností → sestavení odpovědi.

---

## ⚖ Weights / váhy polí
Můžeš přidat „pocitové“ váhy (weighting) mezi poli, např. `name` (název produktu), `description` (popis) apod. — konečný **score** pak bude vážený součet podobností:

<p align="center">
  <img src="./prezentace/weights.png" alt="Váhy pro jednotlivá pole">
</p>

> Tip: u krátkých názvů dej nižší váhu než u popisů, ale vyšší než u volných poznámek.

---

## 🎬 Demo video

> GitHub v README nepřehraje `.mp4`. Použij náhled (thumbnail) → klik → otevře video.

[![Přehrát demo](./prezentace/video_thumbnail.png)](./2025-08-04-09-23-40_w6FGH3gB.mp4)

> Jak vytvořit náhled (volitelné):
> ```bash
> # vybere snímek z 3. sekundy
> ffmpeg -i 2025-08-04-09-23-40_w6FGH3gB.mp4 -ss 00:00:03 -vframes 1 ./prezentace/video_thumbnail.png
> ```

---

## 🚀 Poznámky k nasazení
- **CRON**: spouštěj re-embedding mimo špičku; loguj počet změn a délku běhu.
- **DB**: index nad vektorovým sloupcem (FAISS/pgvector/Annoy/HNSW dle DB) pro rychlé dotazy.
- **Bezpečnost**: ošetři vstupy v chatu, limituj délku dotazu a počty výsledků.
- **Monitoring**: metriky (čas dotazu, přesnost, pokrytí, chybovost) → grafy.