// /api/estrazioni.js (da inserire nella cartella "api")

import { NextResponse } from 'next/server';
import * as cheerio from 'cheerio';

export async function GET(request) {
  const { searchParams } = new URL(request.url);
  const year = searchParams.get('year') || new Date().getFullYear();

  const url = `https://www.lottologia.com/lotto/?do=past-draws-archive&table_view_type=default&year=${year}&numbers=`;

  try {
    const res = await fetch(url, {
      headers: {
        'User-Agent': 'Mozilla/5.0'
      }
    });

    if (!res.ok) {
      return NextResponse.json({ error: 'Errore nel recupero del sito' }, { status: 500 });
    }

    const html = await res.text();
    const $ = cheerio.load(html);

    const tables = $('table');
    if (!tables || tables.length === 0) {
      return NextResponse.json({ error: 'Tabella non trovata' }, { status: 404 });
    }

    const result = [];
    tables.first().find('tr').each((_, row) => {
      const estrazione = [];
      $(row).find('td').each((_, cell) => {
        estrazione.push($(cell).text().trim());
      });
      if (estrazione.length > 0) result.push(estrazione);
    });

    return NextResponse.json({ year, data: result });

  } catch (error) {
    return NextResponse.json({ error: 'Errore server: ' + error.message }, { status: 500 });
  }
}
