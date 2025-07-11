// api/estrazioni.js
import { JSDOM } from "jsdom";

export default async function handler(req, res) {
  const year = req.query.year || new Date().getFullYear();
  const url = `https://www.lottologia.com/lotto/?do=past-draws-archive&table_view_type=default&year=${year}&numbers=`;

  try {
    const response = await fetch(url, {
      headers: { 'User-Agent': 'Mozilla/5.0' }
    });
    const html = await response.text();
    const dom = new JSDOM(html);
    const doc = dom.window.document;

    const table = doc.querySelector("table");
    if (!table) return res.status(500).json({ error: "Nessuna tabella trovata" });

    const rows = [...table.querySelectorAll("tr")].map(tr => {
      return [...tr.querySelectorAll("th, td")].map(td => td.textContent.trim());
    });

    res.status(200).json(rows);
  } catch (error) {
    res.status(500).json({ error: error.toString() });
  }
}