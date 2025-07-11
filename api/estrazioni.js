const axios = require('axios');
const cheerio = require('cheerio');

module.exports = async (req, res) => {
  const year = req.query.year || new Date().getFullYear();

  try {
    const url = `https://www.lottologia.com/lotto/?do=past-draws-archive&table_view_type=default&year=${year}&numbers=`;
    const response = await axios.get(url, {
      headers: {
        'User-Agent': 'Mozilla/5.0'
      }
    });

    const $ = cheerio.load(response.data);
    const table = $('table');

    if (!table || table.length === 0) {
      return res.status(500).json({ error: 'Tabella non trovata nel sito' });
    }

    const tableHTML = $.html(table);
    return res.status(200).json({ html: tableHTML, year });
  } catch (err) {
    return res.status(500).json({ error: 'Errore durante il parsing', details: err.message });
  }
};
