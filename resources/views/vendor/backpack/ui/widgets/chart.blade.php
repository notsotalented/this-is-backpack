@includeWhen(!empty($widget['wrapper']), 'backpack::widgets.inc.wrapper_start')
<div class="{{ $widget['class'] ?? 'chart mb-2' }}">
    <h3 class="card-title text-center">Revenue Completed</h3>
    <input type="date" class="change_date" data="revenue_completed">
    <div><canvas id="revenue_completed"></canvas></div>

    <h3 class="card-title mt-6 text-center">Revenue Not Completed</h3>
    <input type="date" class="change_date" data="revenue_not_completed">
    <div><canvas id="revenue_not_completed"></canvas></div>

    <h3 class="card-title mt-6 text-center">Expenditure Completed</h3>
    <input type="date" class="change_date" data="expenditure_completed">
    <div><canvas id="expenditure_completed"></canvas></div>

    <h3 class="card-title mt-6 text-center">Expenditure Not Completed</h3>
    <input type="date" class="change_date" data="expenditure_not_completed">
    <div><canvas id="expenditure_not_completed"></canvas></div>
</div>
@includeWhen(!empty($widget['wrapper']), 'backpack::widgets.inc.wrapper_end')

@push('after_styles')
    <style>
    </style>
@endpush

@push('after_scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.js"
        integrity="sha512-QSkVNOCYLtj73J4hbmVoOV6KVZuMluZlioC+trLpewV8qMjsWqlIQvkn1KGX2StWvPMdWGBqim1xlC8krl1EKQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        const data = @json($widget['datasets']);

        const ctx1 = document.getElementById('revenue_completed').getContext('2d');
        const ctx2 = document.getElementById('revenue_not_completed').getContext('2d');
        const ctx3 = document.getElementById('expenditure_completed').getContext('2d');
        const ctx4 = document.getElementById('expenditure_not_completed').getContext('2d');

        // chia data thành 4 mảng riêng biệt để vẽ chart cho dễ quản lý code hơn :D
        let data1 = groupDataInSameDay(data.revenue.completed);
        let data2 = groupDataInSameDay(data.revenue.not_completed);
        let data3 = groupDataInSameDay(data.expenditure.completed);
        let data4 = groupDataInSameDay(data.expenditure.not_completed);


        // lấy data từ controller truyền sang view bằng ajax
        $('.change_date').on('change', function() {
            let type = null;
            let is_completed = null;
            if ($(this).attr('data') == 'revenue_completed' || $(this).attr('data') == 'revenue_not_completed') {
                type = "Receive";
                is_completed = $(this).attr('data') == 'revenue_completed' ? 1 : 0;
            } else {
                type = "Transfer";
                is_completed = $(this).attr('data') == 'expenditure_completed' ? 1 : 0;
            }
            const d = new Date($(this).val());
            let month = d.getMonth() + 1;
            let year = d.getFullYear();

            var url = '{{ route('get_transaction_by_user_ajax') }}';
            var params = {
                'month': month,
                'year': year,
                'type': type,
                'is_completed': is_completed
            };
            $.ajax({
                url: url,
                type: 'GET',
                data: params,
                success: function(response) {
                    if (response.status == 'success') {
                        if (type == "Receive" && is_completed == 1) {
                            data1 = groupDataInSameDay(response.data);
                            chart1.data.labels = data1.map(row => getDate(row.created_at));
                            chart1.data.datasets[0].data = data1.map(row => row.money);
                            chart1.update();
                        } else if (type == "Receive" && is_completed == 0) {
                            data2 = groupDataInSameDay(response.data);
                            chart2.data.labels = data2.map(row => getDate(row.created_at));
                            chart2.data.datasets[0].data = data2.map(row => row.money);
                            chart2.update();
                        } else if (type == "Transfer" && is_completed == 1) {
                            data3 = groupDataInSameDay(response.data);
                            chart3.data.labels = data3.map(row => getDate(row.created_at));
                            chart3.data.datasets[0].data = data3.map(row => row.money);
                            chart3.update();
                        } else {
                            data4 = groupDataInSameDay(response.data);
                            chart4.data.labels = data4.map(row => getDate(row.created_at));
                            chart4.data.datasets[0].data = data4.map(row => row.money);
                            chart4.update();
                        }
                    } else {
                        console.log(response);
                    }
                },
                error: function(response) {
                    console.log(response);
                }
            });
        })


        function groupDataInSameDay(data) {
            // cập nhật lại data truyền vào để cộng tiền của các giao dịch cùng ngày lại với nhau
            console.log('data', data)
            // tạo mảng mới để lưu data sau khi đã cộng tiền
            let newData = [];

            // duyệt qua từng phần tử trong mảng data
            for (let i = 0; i < data.length; i++) {
                // lấy ra ngày tháng năm của phần tử đang duyệt
                const d = new Date(data[i].created_at);
                const date = d.getDate() + '/' + (d.getMonth() + 1) + '/' + d.getFullYear();

                // tạo biến để lưu vị trí của phần tử trong mảng newData
                let index = null;

                // duyệt qua từng phần tử trong mảng newData để kiểm tra xem phần tử đang duyệt có cùng ngày với phần tử đang duyệt trong mảng data hay không
                for (let j = 0; j < newData.length; j++) {
                    // lấy ra ngày tháng năm của phần tử đang duyệt trong mảng newData
                    const d2 = new Date(newData[j].created_at);
                    const date2 = d2.getDate() + '/' + (d2.getMonth() + 1) + '/' + d2.getFullYear();

                    // nếu ngày tháng năm của phần tử đang duyệt trong mảng data trùng với ngày tháng năm của phần tử đang duyệt trong mảng newData thì cộng tiền của 2 phần tử lại với nhau
                    if (date == date2) {
                        newData[j].money += data[i].money;
                        index = j;
                        break;
                    }
                }

                // nếu không tìm thấy phần tử nào trong mảng newData có cùng ngày tháng năm với phần tử đang duyệt trong mảng data thì thêm phần tử đó vào mảng newData
                if (index == null) {
                    newData.push(data[i]);
                }
                console.log(data[i].id, index)
            }

            return newData;
        }

        // tách từng mảng theo ngày tháng năm để vẽ chart
        function getDate(date) {
            const d = new Date(date);
            return d.getDate() + '/' + (d.getMonth() + 1) + '/' + d.getFullYear();
        }

        var chart1 = new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: data1.map(row => getDate(row.created_at)), // <======= Here I set the x-axis
                datasets: [{
                    label: 'Total',
                    data: data1.map(row => row.money), // <======= Here I set the y-axis
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
            }
        });

        var chart2 = new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: data2.map(row => getDate(row.created_at)), // <======= Here I set the x-axis
                datasets: [{
                    label: 'Total',
                    data: data2.map(row => row.money), // <======= Here I set the y-axis
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        var chart3 = new Chart(ctx3, {
            type: 'bar',
            data: {
                labels: data3.map(row => getDate(row.created_at)), // <======= Here I set the x-axis
                datasets: [{
                    label: 'Total',
                    data: data3.map(row => row.money), // <======= Here I set the y-axis
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        var chart4 = new Chart(ctx4, {
            type: 'bar',
            data: {
                labels: data4.map(row => getDate(row.created_at)), // <======= Here I set the x-axis
                datasets: [{
                    label: 'Total',
                    data: data4.map(row => row.money), // <======= Here I set the y-axis
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
@endpush
