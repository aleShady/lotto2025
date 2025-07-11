export default function handler(req, res) {
  res.status(200).json({ message: "Microservizio attivo!", year: req.query.year || "Nessun anno passato" });
}