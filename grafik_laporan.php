import { useState } from "react";
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer, LineChart, Line, PieChart, Pie, Cell } from "recharts";

const FinancialChart = () => {
  // Sample data - dalam implementasi sebenarnya ini akan diambil dari database PHP
  const [financialData, setFinancialData] = useState([
    {
      bulan: "Januari",
      penerimaan: 150000000,
      beban: 120000000,
      labaRugi: 30000000
    },
    {
      bulan: "Februari", 
      penerimaan: 180000000,
      beban: 135000000,
      labaRugi: 45000000
    },
    {
      bulan: "Maret",
      penerimaan: 165000000,
      beban: 140000000,
      labaRugi: 25000000
    },
    {
      bulan: "April",
      penerimaan: 190000000,
      beban: 155000000,
      labaRugi: 35000000
    },
    {
      bulan: "Mei",
      penerimaan: 175000000,
      beban: 130000000,
      labaRugi: 45000000
    },
    {
      bulan: "Juni",
      penerimaan: 200000000,
      beban: 160000000,
      labaRugi: 40000000
    }
  ]);

  // Data untuk pie chart
  const currentMonth = financialData[financialData.length - 1];
  const pieData = [
    {
      name: "Penerimaan Bersih",
      value: currentMonth.labaRugi,
      color: "#22c55e"
    },
    {
      name: "Beban",
      value: currentMonth.beban,
      color: "#ef4444"
    }
  ];

  // Format currency
  const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0
    }).format(value);
  };

  // Custom tooltip
  const CustomTooltip = ({ active, payload, label }) => {
    if (active && payload && payload.length) {
      return (
        <div className="bg-white p-4 border border-gray-200 rounded-lg shadow-lg">
          <p className="font-semibold text-gray-800">{label}</p>
          {payload.map((entry, index) => (
            <p key={index} style={{ color: entry.color }} className="text-sm">
              {entry.dataKey === 'penerimaan' && 'Penerimaan: '}
              {entry.dataKey === 'beban' && 'Beban: '}
              {entry.dataKey === 'labaRugi' && 'Laba/Rugi: '}
              {formatCurrency(entry.value)}
            </p>
          ))}
        </div>
      );
    }
    return null;
  };

  // Calculate totals
  const totalPenerimaan = financialData.reduce((sum, item) => sum + item.penerimaan, 0);
  const totalBeban = financialData.reduce((sum, item) => sum + item.beban, 0);
  const totalLabaRugi = financialData.reduce((sum, item) => sum + item.labaRugi, 0);

  return (
    <div className="min-h-screen bg-gray-50 p-6">
      <div className="max-w-7xl mx-auto">
        {/* Header */}
        <div className="mb-8 bg-white rounded-lg shadow-md p-6">
          <h1 className="text-3xl font-bold text-gray-800 mb-2">Laporan Keuangan</h1>
          <p className="text-gray-600">Dashboard Grafik Penerimaan, Beban, dan Laba Rugi</p>
        </div>

        {/* Summary Cards */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <div className="bg-green-50 border-l-4 border-green-500 p-6 rounded-lg shadow-md">
            <div className="flex items-center">
              <div className="flex-1">
                <h3 className="text-green-800 font-semibold text-lg">Total Penerimaan</h3>
                <p className="text-green-600 text-2xl font-bold">{formatCurrency(totalPenerimaan)}</p>
              </div>
              <div className="text-green-500 text-3xl">📈</div>
            </div>
          </div>

          <div className="bg-red-50 border-l-4 border-red-500 p-6 rounded-lg shadow-md">
            <div className="flex items-center">
              <div className="flex-1">
                <h3 className="text-red-800 font-semibold text-lg">Total Beban</h3>
                <p className="text-red-600 text-2xl font-bold">{formatCurrency(totalBeban)}</p>
              </div>
              <div className="text-red-500 text-3xl">📉</div>
            </div>
          </div>

          <div className={`${totalLabaRugi >= 0 ? 'bg-blue-50 border-blue-500' : 'bg-orange-50 border-orange-500'} border-l-4 p-6 rounded-lg shadow-md`}>
            <div className="flex items-center">
              <div className="flex-1">
                <h3 className={`${totalLabaRugi >= 0 ? 'text-blue-800' : 'text-orange-800'} font-semibold text-lg`}>
                  {totalLabaRugi >= 0 ? 'Total Laba' : 'Total Rugi'}
                </h3>
                <p className={`${totalLabaRugi >= 0 ? 'text-blue-600' : 'text-orange-600'} text-2xl font-bold`}>
                  {formatCurrency(Math.abs(totalLabaRugi))}
                </p>
              </div>
              <div className={`${totalLabaRugi >= 0 ? 'text-blue-500' : 'text-orange-500'} text-3xl`}>
                {totalLabaRugi >= 0 ? '💰' : '⚠️'}
              </div>
            </div>
          </div>
        </div>

        {/* Charts Grid */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
          {/* Bar Chart */}
          <div className="bg-white p-6 rounded-lg shadow-md">
            <h2 className="text-xl font-semibold text-gray-800 mb-4">Perbandingan Bulanan</h2>
            <ResponsiveContainer width="100%" height={400}>
              <BarChart data={financialData} margin={{ top: 20, right: 30, left: 20, bottom: 5 }}>
                <CartesianGrid strokeDasharray="3 3" stroke="#f0f0f0" />
                <XAxis 
                  dataKey="bulan" 
                  tick={{ fontSize: 12 }}
                  angle={-45}
                  textAnchor="end"
                  height={70}
                />
                <YAxis 
                  tick={{ fontSize: 12 }}
                  tickFormatter={(value) => `${(value / 1000000).toFixed(0)}M`}
                />
                <Tooltip content={<CustomTooltip />} />
                <Legend />
                <Bar dataKey="penerimaan" fill="#22c55e" name="Penerimaan" radius={[4, 4, 0, 0]} />
                <Bar dataKey="beban" fill="#ef4444" name="Beban" radius={[4, 4, 0, 0]} />
              </BarChart>
            </ResponsiveContainer>
          </div>

          {/* Line Chart */}
          <div className="bg-white p-6 rounded-lg shadow-md">
            <h2 className="text-xl font-semibold text-gray-800 mb-4">Trend Laba/Rugi</h2>
            <ResponsiveContainer width="100%" height={400}>
              <LineChart data={financialData} margin={{ top: 20, right: 30, left: 20, bottom: 5 }}>
                <CartesianGrid strokeDasharray="3 3" stroke="#f0f0f0" />
                <XAxis 
                  dataKey="bulan" 
                  tick={{ fontSize: 12 }}
                  angle={-45}
                  textAnchor="end"
                  height={70}
                />
                <YAxis 
                  tick={{ fontSize: 12 }}
                  tickFormatter={(value) => `${(value / 1000000).toFixed(0)}M`}
                />
                <Tooltip content={<CustomTooltip />} />
                <Legend />
                <Line 
                  type="monotone" 
                  dataKey="labaRugi" 
                  stroke="#3b82f6" 
                  strokeWidth={3}
                  dot={{ fill: '#3b82f6', strokeWidth: 2, r: 6 }}
                  activeDot={{ r: 8, stroke: '#3b82f6', strokeWidth: 2 }}
                  name="Laba/Rugi"
                />
              </LineChart>
            </ResponsiveContainer>
          </div>

          {/* Pie Chart */}
          <div className="bg-white p-6 rounded-lg shadow-md">
            <h2 className="text-xl font-semibold text-gray-800 mb-4">Komposisi Bulan Terakhir</h2>
            <ResponsiveContainer width="100%" height={400}>
              <PieChart>
                <Pie
                  data={pieData}
                  cx="50%"
                  cy="50%"
                  outerRadius={120}
                  fill="#8884d8"
                  dataKey="value"
                  label={({ name, percent }) => `${name} ${(percent * 100).toFixed(1)}%`}
                  labelLine={false}
                >
                  {pieData.map((entry, index) => (
                    <Cell key={`cell-${index}`} fill={entry.color} />
                  ))}
                </Pie>
                <Tooltip formatter={(value) => [formatCurrency(value), '']} />
              </PieChart>
            </ResponsiveContainer>
          </div>

          {/* Data Table */}
          <div className="bg-white p-6 rounded-lg shadow-md">
            <h2 className="text-xl font-semibold text-gray-800 mb-4">Data Detail</h2>
            <div className="overflow-x-auto">
              <table className="w-full text-sm">
                <thead>
                  <tr className="border-b-2 border-gray-200">
                    <th className="text-left py-3 px-2 font-semibold text-gray-700">Bulan</th>
                    <th className="text-right py-3 px-2 font-semibold text-gray-700">Penerimaan</th>
                    <th className="text-right py-3 px-2 font-semibold text-gray-700">Beban</th>
                    <th className="text-right py-3 px-2 font-semibold text-gray-700">Laba/Rugi</th>
                  </tr>
                </thead>
                <tbody>
                  {financialData.map((item, index) => (
                    <tr key={index} className="border-b border-gray-100 hover:bg-gray-50">
                      <td className="py-3 px-2 font-medium text-gray-800">{item.bulan}</td>
                      <td className="py-3 px-2 text-right text-green-600">
                        {formatCurrency(item.penerimaan)}
                      </td>
                      <td className="py-3 px-2 text-right text-red-600">
                        {formatCurrency(item.beban)}
                      </td>
                      <td className={`py-3 px-2 text-right font-semibold ${item.labaRugi >= 0 ? 'text-blue-600' : 'text-orange-600'}`}>
                        {formatCurrency(item.labaRugi)}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        </div>

        {/* Footer Note */}
        <div className="mt-8 bg-gray-100 p-4 rounded-lg">
          <p className="text-sm text-gray-600 text-center">
            💡 <strong>Catatan:</strong> Grafik ini menampilkan data sampel. Dalam implementasi sebenarnya, 
            data akan diambil dari database PHP Anda menggunakan fungsi yang sudah ada seperti getTotalByKelompok().
          </p>
        </div>
      </div>
    </div>
  );
};

export default FinancialChart;